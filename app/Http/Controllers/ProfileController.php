<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'tabs' => self::TABS,
            'activeTab' => $this->tabFrom($request),
        ]);
    }

    /** @var array<string, string> */
    public const TABS = [
        'identity' => 'Identity',
        'contact' => 'Contact',
        'roles' => 'Roles',
        'public' => 'Public',
        'password' => 'Password',
        'notifications' => 'Notifications',
        'api' => 'API Key',
    ];

    private function tabFrom(Request $request): string
    {
        $tab = (string) $request->input('tab', 'identity');

        return array_key_exists($tab, self::TABS) ? $tab : 'identity';
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit', ['tab' => $this->tabFrom($request)])->with('status', 'profile-updated');
    }

}
