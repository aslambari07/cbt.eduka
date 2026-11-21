<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $results = ExamResult::query()->where('student_id', $request->user()->id)->where(function ($query): void {
            $query->where('status', 'published')->orWhere(fn ($immediate) => $immediate->where('status', 'graded')->whereHas('exam', fn ($exam) => $exam->where('results_visibility', 'immediate')));
        })->with('exam.subject')->latest('published_at')->paginate(12);

        return view('student.results.index', compact('results'));
    }

    public function show(Request $request, ExamResult $result): View
    {
        $this->authorize('view', $result);
        $result->load(['exam.subject', 'details.question', 'certificate']);

        return view('student.results.show', compact('result'));
    }
}
