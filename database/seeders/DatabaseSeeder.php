<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\ExamResultStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Institution;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || ! config('cbt.demo.enabled')) {
            return;
        }

        $demoPassword = config('cbt.demo.password');
        if (blank($demoPassword)) {
            throw new \LogicException('CBT_DEMO_PASSWORD wajib diisi untuk menjalankan demo seeder lokal/testing.');
        }

        $institution = Institution::query()->updateOrCreate(
            ['code' => 'DEMO-EDUKA'],
            [
                'name' => 'SMA Demo CBT.EDUKA',
                'contact_email' => config('cbt.demo.accounts.admin.email'),
                'address' => 'Bandung',
                'status' => 'active',
            ],
        );

        $admin = $this->upsertDemoUser('admin', Role::ADMIN, $institution->id, $demoPassword);
        $teacher = $this->upsertDemoUser('teacher', Role::TEACHER, $institution->id, $demoPassword);
        $student = $this->upsertDemoUser('student', Role::STUDENT, $institution->id, $demoPassword);

        $teacher->teacherProfile()->updateOrCreate([], ['employee_number' => 'T-DEMO-'.$teacher->id, 'specialization' => 'Matematika']);
        $student->studentProfile()->updateOrCreate([], ['student_number' => 'S-DEMO-'.$student->id, 'grade' => 'XII', 'major' => 'IPA']);

        $math = Subject::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'code' => 'MTK-XII'],
            ['name' => 'Matematika', 'description' => 'Limit, turunan, dan persamaan kuadrat.', 'status' => 'active'],
        );

        $class = SchoolClass::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'name' => 'XII IPA 2'],
            ['level' => 'XII', 'status' => 'active', 'homeroom_teacher_id' => $teacher->id],
        );

        $class->subjects()->syncWithoutDetaching([$math->id]);
        $class->teachers()->syncWithoutDetaching([$teacher->id => ['subject_id' => $math->id]]);
        $class->students()->syncWithoutDetaching([$student->id => ['joined_at' => now()]]);

        $question = Question::query()->updateOrCreate(
            ['author_id' => $teacher->id, 'prompt' => 'Jika x² + 3x - 10 = 0, maka nilai x adalah...'],
            ['subject_id' => $math->id, 'type' => QuestionType::MULTIPLE_CHOICE, 'explanation' => 'Faktorkan menjadi (x + 5)(x - 2) = 0.', 'definition' => ['correct_option' => 'A'], 'points' => 2, 'difficulty' => 'medium', 'status' => 'published'],
        );
        $question->options()->updateOrCreate(['key' => 'A'], ['label' => 'x = 2 atau x = -5', 'is_correct' => true, 'sort_order' => 1]);
        $question->options()->updateOrCreate(['key' => 'B'], ['label' => 'x = 3 atau x = -2', 'is_correct' => false, 'sort_order' => 2]);
        $question->options()->updateOrCreate(['key' => 'C'], ['label' => 'x = -3 atau x = 2', 'is_correct' => false, 'sort_order' => 3]);
        $question->options()->updateOrCreate(['key' => 'D'], ['label' => 'x = 1 atau x = -4', 'is_correct' => false, 'sort_order' => 4]);

        $completedExam = Exam::query()->updateOrCreate(
            ['code' => 'DEMO-EXAM-MTK-001'],
            ['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $math->id, 'class_id' => $class->id, 'name' => 'Persamaan Kuadrat', 'description' => 'Ujian demonstrasi yang sudah selesai.', 'instructions' => 'Pilih jawaban yang paling tepat.', 'duration_minutes' => 30, 'starts_at' => now()->subDays(2), 'ends_at' => now()->subDays(2)->addHour(), 'total_points' => 2, 'passing_grade' => 75, 'max_attempts' => 1, 'status' => ExamStatus::COMPLETED, 'navigation_mode' => 'free', 'results_visibility' => 'after_review'],
        );
        $completedExam->questions()->sync([$question->id => ['sort_order' => 1, 'points' => 2, 'required' => true]]);
        $completedAssignment = $completedExam->assignments()->updateOrCreate(['student_id' => $student->id], ['status' => 'completed', 'max_attempts' => 1, 'assigned_at' => now()->subDays(3)]);
        $completedSession = ExamSession::query()->updateOrCreate(
            ['session_key' => 'demo-session-mtk-001-'.$student->id],
            ['exam_id' => $completedExam->id, 'assignment_id' => $completedAssignment->id, 'student_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionStatus::COMPLETED, 'started_at' => now()->subDays(2), 'expires_at' => now()->subDays(2)->addMinutes(30), 'submitted_at' => now()->subDays(2)->addMinutes(20), 'last_seen_at' => now()->subDays(2)->addMinutes(20), 'question_order' => [$question->id], 'option_orders' => [(string) $question->id => ['A', 'B', 'C', 'D']], 'version' => 1],
        );
        $completedSession->answers()->updateOrCreate(['question_id' => $question->id], ['answer' => 'A', 'version' => 1, 'status' => 'saved', 'grading_status' => 'auto_graded', 'score' => 2, 'saved_at' => now()->subDays(2)]);
        $result = ExamResult::query()->updateOrCreate(['exam_session_id' => $completedSession->id], ['exam_id' => $completedExam->id, 'student_id' => $student->id, 'status' => ExamResultStatus::PUBLISHED, 'total_points' => 2, 'earned_points' => 2, 'score' => 100, 'passing_grade' => 75, 'is_passed' => true, 'graded_at' => now()->subDays(2), 'published_at' => now()->subDays(2)]);
        $result->details()->updateOrCreate(['question_id' => $question->id], ['exam_answer_id' => $completedSession->answers()->first()->id, 'max_points' => 2, 'earned_points' => 2, 'grading_status' => 'auto_graded']);

        $availableExam = Exam::query()->updateOrCreate(
            ['code' => 'DEMO-EXAM-MTK-002'],
            ['owner_id' => $teacher->id, 'institution_id' => $institution->id, 'subject_id' => $math->id, 'class_id' => $class->id, 'name' => 'Limit dan Turunan', 'description' => 'Ujian demonstrasi yang siap dikerjakan.', 'instructions' => 'Jawaban tersimpan otomatis.', 'duration_minutes' => 60, 'starts_at' => now()->subMinutes(10), 'ends_at' => now()->addHours(2), 'total_points' => 2, 'passing_grade' => 75, 'max_attempts' => 1, 'status' => ExamStatus::ONGOING, 'navigation_mode' => 'free', 'results_visibility' => 'after_review'],
        );
        $availableExam->questions()->sync([$question->id => ['sort_order' => 1, 'points' => 2, 'required' => true]]);
        $availableExam->assignments()->updateOrCreate(['student_id' => $student->id], ['status' => 'assigned', 'max_attempts' => 1, 'assigned_at' => now()]);

        unset($admin);
    }

    private function upsertDemoUser(string $account, Role $role, int $institutionId, string $password): User
    {
        $definition = config("cbt.demo.accounts.{$account}");

        return User::query()->updateOrCreate(
            ['email' => $definition['email']],
            [
                'name' => $definition['name'],
                'role' => $role,
                'status' => AccountStatus::ACTIVE,
                'institution_id' => $institutionId,
                'password' => Hash::make($password),
            ],
        );
    }
}
