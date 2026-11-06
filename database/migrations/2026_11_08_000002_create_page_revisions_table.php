<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 200);
            $table->text('body');
            $table->dateTime('published_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['page_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
