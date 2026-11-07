<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'registrationOpen' => SiteSetting::registrationOpen(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_open' => ['required', 'in:0,1'],
        ]);

        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, $data['registration_open']);

        AuditLog::record('settings.registration', $request->user()->id, [
            'registration_open' => $data['registration_open'] === '1',
        ]);

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
