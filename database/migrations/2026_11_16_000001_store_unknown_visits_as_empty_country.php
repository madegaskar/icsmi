<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Moves NULL (unknown country) rows into one '' row per day, so the unique index covers them.
     */
    public function up(): void
    {
        $days = DB::table('page_visits')->whereNull('country_code')->distinct()->pluck('visited_on');

        foreach ($days as $day) {
            $unknown = DB::table('page_visits')->where('visited_on', $day)->whereNull('country_code');
            $total = (int) $unknown->sum('count');
            $unknown->delete();

            $existing = DB::table('page_visits')->where('visited_on', $day)->where('country_code', '');
            if ($existing->increment('count', $total) === 0) {
                DB::table('page_visits')->insert(['visited_on' => $day, 'country_code' => '', 'count' => $total]);
            }
        }
    }

    public function down(): void
    {
        DB::table('page_visits')->where('country_code', '')->update(['country_code' => null]);
    }
};
