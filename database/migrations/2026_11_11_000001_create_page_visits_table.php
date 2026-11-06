<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per day and country. No IP address is stored.
     */
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visited_on');
            $table->char('country_code', 2)->nullable();
            $table->unsignedInteger('count')->default(0);

            $table->unique(['visited_on', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
