<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('public_submissions', function (Blueprint $table) {
            $table->dropIndex(['handled_at']);
            $table->dropColumn('handled_at');
        });

        Schema::table('public_submissions', function (Blueprint $table) {
            $table->string('title')->default('');
            $table->timestamp('confirmed_at')->nullable()->index();
            $table->foreignId('manuscript_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manuscript_id');
            $table->dropIndex(['confirmed_at']);
            $table->dropColumn(['title', 'confirmed_at']);
        });

        Schema::table('public_submissions', function (Blueprint $table) {
            $table->timestamp('handled_at')->nullable()->index();
        });
    }
};
