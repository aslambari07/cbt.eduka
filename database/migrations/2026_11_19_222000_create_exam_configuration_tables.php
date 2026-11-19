<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('name');
            $table->string('code', 80)->unique();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->decimal('total_points', 8, 2)->default(0);
            $table->decimal('passing_grade', 5, 2)->default(75);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->string('navigation_mode', 30)->default('free');
            $table->string('results_visibility', 30)->default('after_review');
            $table->string('status', 30)->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['institution_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('exam_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('sort_order');
            $table->decimal('points', 8, 2)->nullable();
            $table->boolean('required')->default(true);
            $table->timestamps();
            $table->unique(['exam_id', 'question_id']);
            $table->unique(['exam_id', 'sort_order']);
        });

        Schema::create('exam_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('assigned')->index();
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_assignments');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
