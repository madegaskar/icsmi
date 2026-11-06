<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl((string) config('app.url'));
        }

        RateLimiter::for('public-submission', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perDay(30)->by($request->ip()),
            Limit::perDay(5)->by('email:'.mb_strtolower((string) $request->input('email'))),
        ]);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->numbers()
            : Password::min(8));

        Event::listen(Login::class, fn (Login $event) => AuditLog::record('auth.login', $event->user->getAuthIdentifier()));
        Event::listen(Logout::class, fn (Logout $event) => AuditLog::record('auth.logout', $event->user?->getAuthIdentifier()));
        Event::listen(Failed::class, fn (Failed $event) => AuditLog::record('auth.failed', $event->user?->getAuthIdentifier(), [
            'email' => mb_substr((string) ($event->credentials['email'] ?? ''), 0, 255),
        ]));
        Event::listen(Lockout::class, fn (Lockout $event) => AuditLog::record('auth.lockout', null, [
            'email' => mb_substr((string) $event->request->input('email'), 0, 255),
        ]));
        Event::listen(PasswordReset::class, fn (PasswordReset $event) => AuditLog::record('auth.password_reset', $event->user->getAuthIdentifier()));
    }
}
