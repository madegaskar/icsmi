<?php

namespace App\Http\Controllers\Editorial;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    private const KILOBYTES = 1024;

    public function __construct(private readonly MediaUploader $uploader) {}

    public function index(): View
    {
        return view('editor.media.index', [
            'media' => Media::with('uploader:id,name')->latest('id')->paginate(24),
        ]);
    }

    public function create(): View
    {
        return view('editor.media.form', ['media' => new Media]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.$this->maxUploadKilobytes()],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $media = $this->uploader->store($request->file('file'), $data['alt_text'] ?? null, $data['caption'] ?? null, $request->user());

        AuditLog::record('media.uploaded', $request->user()->id, ['uuid' => $media->uuid, 'kind' => $media->kind]);

        return redirect()->route('library.index')->with('status', 'File uploaded.');
    }

    public function edit(Media $media): View
    {
        return view('editor.media.form', ['media' => $media]);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255', Rule::requiredIf($media->isImage())],
            'caption' => ['nullable', 'string', 'max:500'],
        ], [
            'alt_text.required' => 'Alt text is required for images.',
        ]);

        $media->update($data);

        AuditLog::record('media.updated', $request->user()->id, ['uuid' => $media->uuid]);

        return redirect()->route('library.index')->with('status', 'File saved.');
    }

    public function destroy(Request $request, Media $media): RedirectResponse
    {
        $uuid = $media->uuid;

        $this->uploader->delete($media);

        AuditLog::record('media.deleted', $request->user()->id, ['uuid' => $uuid]);

        return redirect()->route('library.index')->with('status', 'File deleted.');
    }

    private function maxUploadKilobytes(): int
    {
        return intdiv(max(config('media.max_image_bytes'), config('media.max_pdf_bytes')), self::KILOBYTES);
    }
}
