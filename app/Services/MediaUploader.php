<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use App\Support\ImageReencoder;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Stores uploaded images and PDFs. Types are judged by the file's content, never by its name.
 */
final class MediaUploader
{
    private const PDF_MIME = 'application/pdf';

    public function __construct(private readonly ImageReencoder $images) {}

    public function store(UploadedFile $file, ?string $altText, ?string $caption, User $uploader): Media
    {
        $mime = $this->detectMime($file);
        $this->ensureWithinSizeLimit($file, $mime);

        if ($mime === self::PDF_MIME) {
            return $this->storePdf($file, $caption, $uploader);
        }

        return $this->storeImage($file, $mime, $altText, $caption, $uploader);
    }

    public function delete(Media $media): void
    {
        Storage::disk(config('media.disk'))->delete(array_values($media->paths));

        $media->delete();
    }

    private function detectMime(UploadedFile $file): string
    {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';
        $allowed = [...array_keys(config('media.image_types')), self::PDF_MIME];

        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'Only JPG, PNG, WebP, or PDF files are allowed.',
            ]);
        }

        return $mime;
    }

    private function ensureWithinSizeLimit(UploadedFile $file, string $mime): void
    {
        $limit = $mime === self::PDF_MIME ? config('media.max_pdf_bytes') : config('media.max_image_bytes');

        if ($file->getSize() > $limit) {
            throw ValidationException::withMessages([
                'file' => 'The file is larger than the allowed size.',
            ]);
        }
    }

    private function storeImage(UploadedFile $file, string $mime, ?string $altText, ?string $caption, User $uploader): Media
    {
        if (blank($altText)) {
            throw ValidationException::withMessages([
                'alt_text' => 'Alt text is required for images.',
            ]);
        }

        $bytes = file_get_contents($file->getRealPath());
        $dimensions = getimagesizefromstring($bytes);

        if ($dimensions === false || $dimensions[0] * $dimensions[1] > config('media.max_image_pixels')) {
            throw ValidationException::withMessages(['file' => 'The image dimensions are too large or unreadable.']);
        }

        $variants = [];

        foreach (config('media.variant_widths') as $variant => $maxWidth) {
            $variants[$variant] = $this->images->reencode($bytes, $mime, $maxWidth)
                ?? throw ValidationException::withMessages(['file' => 'The image could not be read.']);
        }

        return $this->persist($file, Media::KIND_IMAGE, $mime, $variants, [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt_text' => $altText,
            'caption' => $caption,
        ], $uploader);
    }

    private function storePdf(UploadedFile $file, ?string $caption, User $uploader): Media
    {
        $bytes = file_get_contents($file->getRealPath());

        return $this->persist($file, Media::KIND_PDF, self::PDF_MIME, [Media::VARIANT_ORIGINAL => $bytes], [
            'caption' => $caption,
        ], $uploader);
    }

    /**
     * @param  array<string, string>  $variantBytes  Stored bytes keyed by variant name.
     * @param  array<string, mixed>  $details  Attributes that differ per kind.
     */
    private function persist(UploadedFile $file, string $kind, string $mime, array $variantBytes, array $details, User $uploader): Media
    {
        $extension = $kind === Media::KIND_PDF ? 'pdf' : config("media.image_types.{$mime}");
        $baseName = config('media.directory').'/'.Str::random(40);
        $disk = Storage::disk(config('media.disk'));
        $paths = [];

        try {
            foreach ($variantBytes as $variant => $bytes) {
                $path = "{$baseName}-{$variant}.{$extension}";
                $disk->put($path, $bytes);
                $paths[$variant] = $path;
            }

            return Media::create($details + [
                'uuid' => (string) Str::uuid(),
                'original_name' => Str::limit(basename($file->getClientOriginalName()), 200, ''),
                'kind' => $kind,
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'paths' => $paths,
                'uploaded_by' => $uploader->id,
            ]);
        } catch (Throwable $exception) {
            $disk->delete(array_values($paths));

            throw $exception;
        }
    }
}
