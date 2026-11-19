<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRequest;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ExamBuilderService;
use App\Services\ExamLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private readonly ExamBuilderService $builder, private readonly ExamLifecycleService $lifecycle) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);
        $query = Exam::query()->with(['subject', 'schoolClass'])->latest();
        if ($request->user()->hasRole('teacher')) {
            $query->where('owner_id', $request->user()->id);
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('search')->toString().'%')->orWhere('code', 'like', '%'.$request->string('search')->toString().'%'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('teacher.exams.index', ['exams' => $query->paginate(12)->withQueryString()]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Exam::class);

        return view('teacher.exams.create', $this->formData($request));
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $exam = $this->builder->createDraft($request->user(), $data, $data['question_ids'] ?? [], $data['student_ids'] ?? []);

        return redirect()->route('teacher.exams.edit', $exam)->with('status', 'Draft ujian berhasil dibuat.');
    }

    public function edit(Request $request, Exam $exam): View
    {
        $this->authorize('update', $exam);

        return view('teacher.exams.edit', array_merge($this->formData($request), ['exam' => $exam->load(['questions', 'assignments'])]));
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->validated();
        $this->builder->updateDraft($request->user(), $exam, $data, $data['question_ids'] ?? [], $data['student_ids'] ?? []);

        return redirect()->route('teacher.exams.edit', $exam)->with('status', 'Draft ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);
        $exam->delete();

        return redirect()->route('teacher.exams.index')->with('status', 'Ujian diarsipkan.');
    }

    public function transition(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);
        $data = $request->validate(['status' => ['required', 'string']]);
        $status = ExamStatus::tryFrom($data['status']);
        abort_unless($status, 422, 'Status ujian tidak valid.');
        $this->lifecycle->transition($exam, $status);

        return back()->with('status', 'Status ujian diperbarui.');
    }

    private function formData(Request $request): array
    {
        $institutionId = $request->user()->institution_id;

        return [
            'subjects' => Subject::query()->where('institution_id', $institutionId)->orderBy('name')->get(),
            'classes' => SchoolClass::query()->where('institution_id', $institutionId)->with('students')->orderBy('name')->get(),
            'questions' => Question::query()->whereHas('subject', fn ($q) => $q->where('institution_id', $institutionId))->with('subject')->latest()->get(),
            'statuses' => ExamStatus::cases(),
        ];
    }
}
