<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['institution_id', 'slug']);
        });

        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('question_categories')->nullOnDelete();
            $table->string('type', 40)->index();
            $table->text('prompt');
            $table->text('explanation')->nullable();
            $table->json('definition')->nullable();
            $table->decimal('points', 8, 2)->default(1);
            $table->string('difficulty', 20)->default('medium')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['author_id', 'subject_id']);
        });

        Schema::create('question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('key', 10);
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['question_id', 'key']);
        });

        Schema::create('question_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('question_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['institution_id', 'slug']);
        });

        Schema::create('question_tag', function (Blueprint $table): void {
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_tag_id')->constrained('question_tags')->cascadeOnDelete();
            $table->primary(['question_id', 'question_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('question_tags');
        Schema::dropIfExists('question_media');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_categories');
    }
};
