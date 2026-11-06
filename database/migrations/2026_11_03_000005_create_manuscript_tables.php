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
        Schema::create('manuscripts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->foreignId('submitter_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('abstract');
            $table->string('keywords', 500);
            $table->string('status', 20)->index();
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamp('submitted_at')->index();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('manuscript_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('affiliation');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_corresponding')->default(false);
            $table->timestamps();
        });

        Schema::create('manuscript_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('kind', 10);
            $table->string('path');
            $table->string('original_name');
            $table->string('extension', 5);
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->timestamps();

            $table->unique(['manuscript_id', 'version', 'kind']);
        });

        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('note')->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('review_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('round');
            $table->string('status', 20)->index();
            $table->date('due_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('recommendation', 20)->nullable();
            $table->text('comments_for_author')->nullable();
            $table->text('comments_for_editor')->nullable();
            $table->timestamps();

            $table->unique(['manuscript_id', 'reviewer_id', 'round']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_assignments');
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('manuscript_files');
        Schema::dropIfExists('manuscript_authors');
        Schema::dropIfExists('manuscripts');
    }
};
