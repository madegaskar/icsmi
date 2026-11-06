<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable()->after('body');
            $table->softDeletes();
        });

        // Pages that already exist are live, so they keep showing on the site.
        DB::table('pages')->whereNull('published_at')->update(['published_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('published_at');
        });
    }
};
