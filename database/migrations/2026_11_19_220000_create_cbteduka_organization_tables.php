<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->string('contact_email')->nullable();
            $table->string('address')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name', 30);
            $table->string('code', 30);
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
            $table->unique(['institution_id', 'code']);
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'code']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role', 30)->default('student')->after('email')->index();
            $table->string('status', 30)->default('active')->after('role')->index();
        });

        Schema::create('student_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_number', 50)->nullable()->unique();
            $table->string('grade', 30)->nullable();
            $table->string('major', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_number', 50)->nullable()->unique();
            $table->string('specialization')->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('level', 30)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'academic_year_id', 'name']);
        });

        Schema::create('class_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['class_id', 'student_id']);
            $table->index(['student_id', 'class_id']);
        });

        Schema::create('class_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['class_id', 'subject_id']);
        });

        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['class_id', 'teacher_id', 'subject_id']);
            $table->index(['teacher_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('class_members');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id', 'role', 'status']);
        });
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('institutions');
    }
};
