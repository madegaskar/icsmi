<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_consent_at')->nullable()->after('mail_status_updates');
            $table->boolean('mail_announcements')->default(false)->after('privacy_consent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['privacy_consent_at', 'mail_announcements']);
        });
    }
};
