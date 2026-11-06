<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

/**
 * Accepts only real PDF and macro-free DOCX files, judged by content rather than by name.
 */
class ManuscriptDocument implements ValidationRule
{
    public function __construct(private bool $pdfOnly = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = $value instanceof UploadedFile && match (strtolower($value->getClientOriginalExtension())) {
            'pdf' => $this->isPdf($value),
            'docx' => ! $this->pdfOnly && $this->isDocx($value),
            default => false,
        };

        if (! $valid) {
            $fail($this->pdfOnly ? 'The :attribute must be a valid PDF file.' : 'The :attribute must be a valid PDF or DOCX file.');
        }
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
