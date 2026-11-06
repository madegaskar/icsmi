<?php

namespace App\Support;

use GdImage;
use RuntimeException;

/**
 * Decodes an image and encodes it again. Anything that is not pixel data,
 * such as trailing scripts, EXIF blocks, or comments, is dropped in the process.
 */
final class ImageReencoder
{
    /**
     * Returns the re-encoded image, scaled down to at most $maxWidth pixels wide,
     * or null when the bytes are not a decodable image.
     */
    public function reencode(string $bytes, string $mime, int $maxWidth): ?string
    {
        if (getimagesizefromstring($bytes) === false) {
            return null;
        }

        $decoded = imagecreatefromstring($bytes);

        if (! $decoded instanceof GdImage) {
            return null;
        }

        return $this->encode($this->resize($decoded, $maxWidth), $mime);
    }

    private function resize(GdImage $source, int $maxWidth): GdImage
    {
        $sourceWidth = imagesx($source);

        if ($sourceWidth <= $maxWidth) {
            return $source;
        }

        $sourceHeight = imagesy($source);
        $targetHeight = (int) round($sourceHeight * $maxWidth / $sourceWidth);
        $target = imagecreatetruecolor($maxWidth, $targetHeight);

        if (! $target instanceof GdImage) {
            throw new RuntimeException('Could not allocate an image canvas for resizing.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $maxWidth, $targetHeight, $sourceWidth, $sourceHeight);

        return $target;
    }

    private function encode(GdImage $image, string $mime): string
    {
        ob_start();

        $written = match ($mime) {
            'image/jpeg' => imagejpeg($image, null, config('media.jpeg_quality')),
            'image/png' => imagepng($image),
            'image/webp' => imagewebp($image, null, config('media.webp_quality')),
            default => throw new RuntimeException("Unsupported image type: {$mime}"),
        };

        $bytes = ob_get_clean();

        if (! $written || ! is_string($bytes) || $bytes === '') {
            throw new RuntimeException("Could not encode the image as {$mime}.");
        }

        return $bytes;
    }
}
