<?php

namespace App\Http\Controllers\Teacher;

use App\Contracts\QuestionGenerationProvider;
use App\Enums\AiProviderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiGenerationRequest;
use App\Http\Requests\AiQuestionStoreRequest;
use App\Models\Subject;
use App\Services\AiQuestionGeneratorService;
use App\Services\AiQuestionPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiGeneratorController extends Controller
{
    public function __construct(
        private readonly QuestionGenerationProvider $provider,
        private readonly AiQuestionGeneratorService $generator,
        private readonly AiQuestionPersistenceService $persistence,
    ) {}

    public function index(Request $request): View
    {
        $status = $this->provider->status();
        $user = $request->user();

        return view('teacher.ai.generator', [
            'aiStatus' => $status,
            'aiReady' => $status === AiProviderStatus::READY,
            'subjects' => Subject::query()
                ->when(! $user->hasRole('admin'), fn ($query) => $query->where('institution_id', $user->institution_id))
                ->orderBy('name')
                ->get(),
            'generatedQuestions' => session('generated_questions', []),
            'generationParameters' => session('generation_parameters', []),
        ]);
    }

    public function generate(AiGenerationRequest $request): RedirectResponse
    {
        $parameters = $request->validated();
        $result = $this->generator->generate($request->user(), $parameters, $request);

        if (! $result->available) {
            return back()->withInput()->with('ai_status', $result->message)->with('ai_status_code', $result->status->value);
        }

        $questions = collect($result->questions)->map(function (array $question) use ($parameters): array {
            if (! isset($question['subject_id']) && isset($parameters['subject_id'])) {
                $question['subject_id'] = $parameters['subject_id'];
            }

            return $question;
        })->values()->all();

        $message = $result->partial
            ? 'Draft AI berhasil dibuat sebagian dan menunggu review pengajar.'
            : 'Draft AI berhasil dibuat dan menunggu review pengajar.';

        return back()
            ->with('generated_questions', $questions)
            ->with('generation_parameters', $parameters)
            ->with('ai_status', $message)
            ->with('ai_status_code', $result->status->value)
            ->with('ai_metadata', $result->metadata);
    }

    public function store(AiQuestionStoreRequest $request): RedirectResponse
    {
        $count = $this->persistence->store($request->user(), $request->validated('questions'), $request);

        return redirect()->route('teacher.ai.index')
            ->with('ai_status', "{$count} draft soal berhasil disimpan ke bank soal.")
            ->with('ai_status_code', 'SAVED');
    }
}
