<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('total_points', 8, 2)->default(0);
            $table->decimal('earned_points', 8, 2)->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('passing_grade', 5, 2)->default(75);
            $table->boolean('is_passed')->default(false);
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('exam_result_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->foreignId('exam_answer_id')->nullable()->constrained('exam_answers')->nullOnDelete();
            $table->decimal('max_points', 8, 2)->default(0);
            $table->decimal('earned_points', 8, 2)->default(0);
            $table->string('grading_status', 30)->default('pending');
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['exam_result_id', 'question_id']);
        });

        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_result_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('certificate_number', 100)->unique();
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('exam_result_details');
        Schema::dropIfExists('exam_results');
    }
};
