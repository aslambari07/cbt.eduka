<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveExamAnswerRequest;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Services\ExamGradingService;
use App\Services\ExamPublicationService;
use App\Services\ExamSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamSessionController extends Controller
{
    public function __construct(private readonly ExamSessionService $sessions, private readonly ExamGradingService $grading, private readonly ExamPublicationService $publication) {}

    public function start(Request $request, int $exam): RedirectResponse
    {
        $examModel = Exam::query()->findOrFail($exam);
        $session = $this->sessions->start($request->user(), $examModel);

        return redirect()->route('student.sessions.show', $session);
    }

    public function show(Request $request, ExamSession $session): View
    {
        $this->authorize('view', $session);
        $payload = $this->sessions->payload($session, $request->user());

        return view('student.exams.interface', compact('payload', 'session'));
    }

    public function answer(SaveExamAnswerRequest $request, ExamSession $session, Question $question): JsonResponse
    {
        $this->authorize('update', $session);
        $answer = $this->sessions->saveAnswer($session, $request->user(), $question, $request->input('answer'), (int) $request->input('client_version', 0));

        return response()->json(['ok' => true, 'status' => 'saved', 'answer' => $answer->answer, 'version' => $answer->version, 'saved_at' => $answer->saved_at?->toIso8601String()]);
    }

    public function flag(Request $request, ExamSession $session, Question $question): JsonResponse
    {
        $this->authorize('update', $session);
        $flagged = $this->sessions->toggleFlag($session, $request->user(), $question);

        return response()->json(['ok' => true, 'flagged' => $flagged]);
    }

    public function submit(Request $request, ExamSession $session): RedirectResponse
    {
        $this->authorize('update', $session);
        $submitted = $this->sessions->submit($session, $request->user());
        $result = $this->grading->grade($submitted);
        $this->publication->publishAutomatically($result);

        return redirect()->route('student.sessions.show', $session)->with('status', $result->status->value === 'published' ? 'Ujian selesai. Hasil sudah tersedia.' : 'Ujian berhasil dikirim dan sedang diproses.');
    }
}
