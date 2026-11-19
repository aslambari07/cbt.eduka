<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        $assignments = ExamAssignment::query()->where('student_id', $request->user()->id)->with(['exam.subject', 'exam.schoolClass'])->latest('assigned_at')->paginate(12);

        return view('student.exams.index', compact('assignments'));
    }

    public function show(Request $request, Exam $exam): View
    {
        $assignment = $this->assignment($request, $exam);

        return view('student.exams.entry', compact('exam', 'assignment'));
    }

    private function assignment(Request $request, Exam $exam): ExamAssignment
    {
        return ExamAssignment::query()->where('exam_id', $exam->id)->where('student_id', $request->user()->id)->with('exam.subject')->firstOrFail();
    }
}
