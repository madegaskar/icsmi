<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ContentExporter;
use App\Services\ContentImporter;
use App\Services\ContentImportValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentController extends Controller
{
    private const UPLOAD_LIMIT_KILOBYTES = 2048;

    private const PREVIEW_MINUTES = 30;

    private const TOKEN_LENGTH = 40;

    public function __construct(
        private readonly ContentExporter $exporter,
        private readonly ContentImportValidator $validator,
        private readonly ContentImporter $importer,
    ) {}

    public function index(): View
    {
        return view('admin.content.index');
    }

    public function export(Request $request): JsonResponse
    {
        AuditLog::record('content.exported', $request->user()->id);

        return response()->json($this->exporter->export(), 200, [
            'Content-Disposition' => 'attachment; filename="icsmi-content-'.now()->format('Y-m-d').'.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Reads the uploaded file and keeps it for a short time, so the admin can review it before anything changes.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:'.self::UPLOAD_LIMIT_KILOBYTES],
        ]);

        $payload = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['file' => 'The file is not valid JSON.']);
        }

        $errors = $this->validator->validate($payload);

        if ($errors !== []) {
            return back()->withErrors(['content' => $errors]);
        }

        $token = Str::random(self::TOKEN_LENGTH);
        Cache::put($this->cacheKey($request, $token), $payload, now()->addMinutes(self::PREVIEW_MINUTES));

        return redirect()->route('admin.content.preview', $token);
    }

    public function preview(Request $request, string $token): View
    {
        return view('admin.content.preview', [
            'token' => $token,
            'plan' => $this->importer->plan($this->pendingPayload($request, $token)),
        ]);
    }

    public function apply(Request $request, string $token): RedirectResponse
    {
        $payload = $this->pendingPayload($request, $token);

        $this->importer->apply($payload, $request->user());

        Cache::forget($this->cacheKey($request, $token));

        return redirect()->route('admin.content.index')->with('status', 'Content imported.');
    }

    /**
     * @return array<string, mixed>
     */
    private function pendingPayload(Request $request, string $token): array
    {
        $payload = Cache::get($this->cacheKey($request, $token));

        abort_unless(is_array($payload), 404);

        return $payload;
    }

    private function cacheKey(Request $request, string $token): string
    {
        return 'content-import:'.$request->user()->id.':'.$token;
    }
}
