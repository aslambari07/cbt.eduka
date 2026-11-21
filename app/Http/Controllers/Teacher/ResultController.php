<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeResultRequest;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamResultDetail;
use App\Services\CsvExportService;
use App\Services\ExamGradingService;
use App\Services\ExamPublicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    public function __construct(private readonly ExamPublicationService $publication, private readonly ExamGradingService $grading, private readonly CsvExportService $csv) {}

    public function index(Request $request, Exam $exam): View
    {
        $this->authorize('view', $exam);
        $results = ExamResult::query()->where('exam_id', $exam->id)->with('student')->latest()->paginate(25);

        return view('teacher.results.index', compact('exam', 'results'));
    }

    public function show(Request $request, ExamResult $result): View
    {
        $this->authorize('view', $result);
        $result->load(['exam', 'student', 'details.question']);

        return view('teacher.results.show', compact('result'));
    }

    public function export(Request $request, Exam $exam): StreamedResponse
    {
        $this->authorize('view', $exam);
        $results = ExamResult::query()->where('exam_id', $exam->id)->with('student')->orderBy('id')->cursor();

        return $this->csv->download('cbt-eduka-results-'.$exam->code.'-'.now()->format('Ymd-His').'.csv', ['ID Hasil', 'Peserta', 'Email', 'Nilai', 'Lulus', 'Status'], $this->resultRows($results));
    }

    public function publish(Request $request, ExamResult $result): RedirectResponse
    {
        $this->authorize('publish', $result);
        $this->publication->publish($request->user(), $result);

        return back()->with('status', 'Hasil berhasil dipublikasikan.');
    }

    public function grade(GradeResultRequest $request, ExamResultDetail $detail): RedirectResponse
    {
        $detail->load('result.exam');
        $this->authorize('grade', $detail->result);
        $this->grading->gradeManual($request->user(), $detail, (float) $request->validated('score'), $request->validated('feedback'));

        return back()->with('status', 'Nilai manual berhasil disimpan.');
    }

    private function resultRows(iterable $results): iterable
    {
        foreach ($results as $result) {
            yield [$result->id, $result->student->name, $result->student->email, (float) $result->score, $result->is_passed ? 'Ya' : 'Tidak', $result->status->value];
        }
    }
}
