<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\ManuscriptFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManuscriptFileController extends Controller
{
    public function __invoke(ManuscriptFile $file): StreamedResponse
    {
        Gate::authorize('download', $file);

        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->downloadName(), [
            'Content-Type' => ManuscriptFile::MIME_TYPES[$file->extension],
        ]);
    }
}
