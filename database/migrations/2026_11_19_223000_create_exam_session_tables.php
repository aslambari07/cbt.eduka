<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('session_key')->unique();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('exam_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('status', 30)->default('waiting')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->json('question_order')->nullable();
            $table->json('option_orders')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('exam_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->json('answer')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->string('status', 30)->default('saved');
            $table->string('grading_status', 30)->default('pending');
            $table->decimal('score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_session_id', 'question_id']);
            $table->index(['exam_session_id', 'status']);
        });

        Schema::create('exam_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['exam_session_id', 'question_id']);
        });

        Schema::create('exam_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->index();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            $table->index(['exam_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_events');
        Schema::dropIfExists('exam_flags');
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_sessions');
    }
};
