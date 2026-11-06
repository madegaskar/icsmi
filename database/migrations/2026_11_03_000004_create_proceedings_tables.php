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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedInteger('volume')->nullable();
            $table->unsignedInteger('number')->nullable();
            $table->string('conference_dates')->nullable();
            $table->string('location')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('issn', 9)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('abstract');
            $table->string('doi')->nullable();
            $table->unsignedInteger('first_page')->nullable();
            $table->unsignedInteger('last_page')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('article_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('affiliation')->nullable();
            $table->string('orcid', 19)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('article_keyword', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();

            $table->primary(['article_id', 'keyword_id']);
        });

        Schema::create('article_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);

            $table->unique(['article_id', 'date']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('board_members', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20)->index();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_members');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('article_stats');
        Schema::dropIfExists('article_keyword');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('article_authors');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('issues');
    }
};
