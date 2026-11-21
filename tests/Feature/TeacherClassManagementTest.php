<?php

namespace Tests\Feature;

use App\Enums\ExamSessionStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeacherClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_add_and_remove_student_from_assigned_class(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $class = SchoolClass::factory()->create(['institution_id' => $institution->id]);
        $class->teachers()->attach($teacher->id);

        $this->actingAs($teacher)->post(route('teacher.classes.students.store', $class), ['student_id' => $student->id])->assertRedirect();
        $this->assertDatabaseHas('class_members', ['class_id' => $class->id, 'student_id' => $student->id]);

        $this->actingAs($teacher)->delete(route('teacher.classes.students.destroy', [$class, $student]))->assertRedirect();
        $this->assertDatabaseMissing('class_members', ['class_id' => $class->id, 'student_id' => $student->id]);
    }

    public function test_teacher_cannot_view_or_modify_unassigned_class(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $class = SchoolClass::factory()->create(['institution_id' => $institution->id]);

        $this->actingAs($teacher)->get(route('teacher.classes.show', $class))->assertForbidden();
        $this->actingAs($teacher)->post(route('teacher.classes.students.store', $class), ['student_id' => User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id])->id])->assertForbidden();
    }

    public function test_teacher_can_view_live_monitoring_for_owned_exam_only(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create(['role' => Role::TEACHER, 'institution_id' => $institution->id]);
        $student = User::factory()->create(['role' => Role::STUDENT, 'institution_id' => $institution->id]);
        $exam = Exam::factory()->create(['owner_id' => $teacher->id, 'institution_id' => $institution->id]);
        $assignment = $exam->assignments()->create(['student_id' => $student->id, 'status' => 'started', 'max_attempts' => 1, 'assigned_at' => now()]);
        ExamSession::create(['session_key' => (string) Str::uuid(), 'exam_id' => $exam->id, 'assignment_id' => $assignment->id, 'student_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionStatus::IN_PROGRESS, 'started_at' => now(), 'expires_at' => now()->addHour(), 'last_seen_at' => now(), 'question_order' => [], 'option_orders' => []]);

        $this->actingAs($teacher)->get(route('teacher.exams.monitor', $exam))->assertOk()->assertSee($student->name);
        $other = Exam::factory()->create();
        $this->actingAs($teacher)->get(route('teacher.exams.monitor', $other))->assertForbidden();
    }
}
