<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public const DESCRIPTION_KEY = 'seo_description';

    public const KEYWORDS_KEY = 'seo_keywords';

    public function edit(): View
    {
        return view('admin.seo.edit', [
            'description' => SiteSetting::get(self::DESCRIPTION_KEY, ''),
            'keywords' => SiteSetting::get(self::KEYWORDS_KEY, ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seo_description' => ['nullable', 'string', 'max:300'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        SiteSetting::put(self::DESCRIPTION_KEY, $data['seo_description'] ?? '');
        SiteSetting::put(self::KEYWORDS_KEY, $data['seo_keywords'] ?? '');

        AuditLog::record('settings.seo', $request->user()->id, []);

        return redirect()->route('admin.seo.edit')->with('status', 'SEO settings saved.');
    }
}
