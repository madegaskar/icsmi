<?php

namespace App\Http\Controllers\Editorial;

use App\Http\Controllers\Controller;
use App\Models\ManuscriptFile;
use App\Models\PublicSubmission;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicSubmissionController extends Controller
{
    public function index(): View
    {
        return view('editor.submissions.index', [
            'submissions' => PublicSubmission::latest('id')->paginate(25),
        ]);
    }

    public function show(PublicSubmission $submission): View
    {
        return view('editor.submissions.show', ['submission' => $submission]);
    }

    public function file(PublicSubmission $submission): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($submission->path), 404);

        return Storage::disk('local')->download($submission->path, $submission->code.'.'.$submission->extension, [
            'Content-Type' => ManuscriptFile::MIME_TYPES[$submission->extension],
        ]);
    }
}
