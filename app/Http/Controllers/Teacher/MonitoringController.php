<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function show(Request $request, Exam $exam): View
    {
        $this->authorize('view', $exam);
        $sessions = $exam->sessions()->with('student')->withCount('answers')->latest('started_at')->paginate(30)->withQueryString();

        return view('teacher.monitoring.show', compact('exam', 'sessions'));
    }
}
