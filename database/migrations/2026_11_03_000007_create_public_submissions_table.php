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
        Schema::create('public_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('institution_address', 500);
            $table->text('abstract');
            $table->string('keywords', 500);
            $table->string('path');
            $table->string('original_name');
            $table->string('extension', 5);
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->timestamp('handled_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_submissions');
    }
};
