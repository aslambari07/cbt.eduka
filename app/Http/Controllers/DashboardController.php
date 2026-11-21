<?php

namespace App\Http\Controllers;

use App\Enums\ExamAssignmentStatus;
use App\Enums\ExamResultStatus;
use App\Enums\ExamStatus;
use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\ExamResult;
use App\Models\Institution;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return match ($request->user()->role) {
            Role::ADMIN => $this->admin(),
            Role::TEACHER => $this->teacher($request),
            Role::STUDENT => $this->student($request),
        };
    }

    private function admin(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(), 'institutions' => Institution::count(), 'teachers' => User::where('role', Role::TEACHER)->count(),
                'students' => User::where('role', Role::STUDENT)->count(), 'classes' => SchoolClass::count(), 'subjects' => Subject::count(),
                'exams' => Exam::count(), 'activeExams' => Exam::whereIn('status', [ExamStatus::PUBLISHED, ExamStatus::ONGOING])->count(),
            ],
            'recentExams' => Exam::with(['subject', 'owner'])->latest()->limit(6)->get(),
        ]);
    }

    private function teacher(Request $request): View
    {
        $user = $request->user();
        $examQuery = Exam::where('owner_id', $user->id);
        $classIds = $user->taughtClasses()->pluck('classes.id');
        $studentCount = DB::table('class_members')->whereIn('class_id', $classIds)->distinct('student_id')->count('student_id');

        return view('teacher.dashboard', [
            'stats' => [
                'classes' => $classIds->count(), 'students' => $studentCount, 'exams' => (clone $examQuery)->count(),
                'activeExams' => (clone $examQuery)->whereIn('status', [ExamStatus::PUBLISHED, ExamStatus::ONGOING])->count(),
                'questions' => Question::where('author_id', $user->id)->count(),
            ],
            'exams' => $examQuery->with('subject')->latest()->limit(8)->get(),
        ]);
    }

    private function student(Request $request): View
    {
        $user = $request->user();
        $assignments = ExamAssignment::where('student_id', $user->id)->with(['exam.subject'])->get();
        $activeAssignments = $assignments->filter(fn (ExamAssignment $assignment): bool => in_array($assignment->status, [ExamAssignmentStatus::ASSIGNED, ExamAssignmentStatus::STARTED], true));
        $results = ExamResult::where('student_id', $user->id)->where(function ($query): void {
            $query->where('status', ExamResultStatus::PUBLISHED)->orWhere(fn ($immediate) => $immediate->where('status', ExamResultStatus::GRADED)->whereHas('exam', fn ($exam) => $exam->where('results_visibility', 'immediate')));
        })->get();

        return view('student.dashboard', [
            'stats' => [
                'completed' => $results->count(), 'available' => $activeAssignments->count(),
                'average' => round((float) ($results->avg('score') ?? 0), 1), 'inProgress' => $assignments->filter(fn (ExamAssignment $assignment): bool => $assignment->status === ExamAssignmentStatus::STARTED)->count(),
            ],
            'nextExam' => $activeAssignments->sortBy(fn ($assignment) => $assignment->exam->starts_at?->timestamp ?? PHP_INT_MAX)->first(),
            'assignments' => $assignments->sortByDesc('assigned_at')->take(6),
            'results' => $results->sortByDesc('published_at')->take(5),
        ]);
    }
}
