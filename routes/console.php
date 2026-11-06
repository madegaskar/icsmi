<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\PublicSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('icsmi:make-admin {email : Email of an existing account}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error('No account uses that email. Register on the website first.');

        return 1;
    }

    $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
    $user->assignRole(Role::Admin);

    AuditLog::record('user.made_admin', null, ['user_id' => $user->id]);

    $this->info($user->email.' is now an administrator.');

    return 0;
})->purpose('Give an existing account the administrator role');

Artisan::command('icsmi:purge-unconfirmed', function () {
    $expired = PublicSubmission::whereNull('confirmed_at')
        ->where('created_at', '<', now()->subDays(PublicSubmission::CONFIRMATION_DAYS))
        ->get();

    foreach ($expired as $submission) {
        Storage::disk('local')->delete($submission->path);
        $submission->delete();
    }

    $this->info($expired->count().' unconfirmed submissions deleted.');
})->purpose('Delete public submissions that were never confirmed');

Schedule::command('icsmi:purge-unconfirmed')->daily();
