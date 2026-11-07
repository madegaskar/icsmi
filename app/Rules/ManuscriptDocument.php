<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

/**
 * Accepts only real files of the allowed types (PDF and macro-free DOCX by default), judged by content rather than by name.
 */
class ManuscriptDocument implements ValidationRule
{
    /**
     * @param  array<int, string>  $allowed  Extensions to accept: 'pdf', 'docx', or both.
     */
    public function __construct(private array $allowed = ['pdf', 'docx']) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $extension = $value instanceof UploadedFile ? strtolower($value->getClientOriginalExtension()) : '';

        $valid = $value instanceof UploadedFile
            && in_array($extension, $this->allowed, true)
            && match ($extension) {
                'pdf' => $this->isPdf($value),
                'docx' => $this->isDocx($value),
                default => false,
            };

        if (! $valid) {
            $fail('The :attribute must be a valid '.$this->allowedLabel().' file.');
        }
    }

    private function allowedLabel(): string
    {
        return implode(' or ', array_map(strtoupper(...), $this->allowed));
    }

    private function isPdf(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        return $header === '%PDF-';
    }

    private function isDocx(UploadedFile $file): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath(), ZipArchive::RDONLY) !== true) {
            return false;
        }

        $valid = $zip->locateName('[Content_Types].xml') !== false
            && $zip->locateName('word/document.xml') !== false
            && $zip->locateName('word/vbaProject.bin') === false;

        $zip->close();

        return $valid;
    }
}
