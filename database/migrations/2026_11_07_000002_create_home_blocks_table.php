<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('heading', 150)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('button_label', 60)->nullable();
            $table->string('button_url', 255)->nullable();
            $table->foreignId('desktop_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
    }
};
