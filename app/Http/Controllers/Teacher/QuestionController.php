<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Models\Subject;
use App\Services\QuestionDefinitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Question::class);
        $user = $request->user();
        $query = Question::query()->with('subject')->latest();

        if ($user->role->value === 'teacher') {
            $query->where('author_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('prompt', 'like', '%'.$request->string('search')->toString().'%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return view('teacher.questions.index', [
            'questions' => $query->paginate(12)->withQueryString(),
            'subjects' => Subject::query()->where('institution_id', $user->institution_id)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Question::class);

        return view('teacher.questions.create', [
            'subjects' => Subject::query()->where('institution_id', $request->user()->institution_id)->orderBy('name')->get(),
        ]);
    }

    public function store(QuestionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->ensureSubjectBelongsToUser($request, (int) $data['subject_id']);

        DB::transaction(function () use ($request, $data): void {
            $question = Question::create([
                'author_id' => $request->user()->id,
                'subject_id' => $data['subject_id'],
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'prompt' => $data['prompt'],
                'explanation' => $data['explanation'] ?? null,
                'definition' => app(QuestionDefinitionService::class)->build($data),
                'points' => $data['points'],
                'difficulty' => $data['difficulty'],
                'status' => $data['status'],
            ]);

            foreach ($data['options'] ?? [] as $index => $option) {
                $question->options()->create([
                    'key' => $option['key'],
                    'label' => $option['label'],
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('teacher.questions.index')->with('status', 'Soal berhasil disimpan sebagai draft.');
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        return view('teacher.questions.edit', ['question' => $question->load('options'), 'subjects' => Subject::query()->where('institution_id', request()->user()->institution_id)->orderBy('name')->get()]);
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        $data = $request->validated();
        $this->authorize('update', $question);
        $this->ensureSubjectBelongsToUser($request, (int) $data['subject_id']);

        DB::transaction(function () use ($data, $question): void {
            $question->update([
                'subject_id' => $data['subject_id'], 'category_id' => $data['category_id'] ?? null, 'type' => $data['type'],
                'prompt' => $data['prompt'], 'explanation' => $data['explanation'] ?? null, 'definition' => app(QuestionDefinitionService::class)->build($data),
                'points' => $data['points'], 'difficulty' => $data['difficulty'], 'status' => $data['status'],
            ]);
            $question->options()->delete();
            foreach ($data['options'] ?? [] as $index => $option) {
                $question->options()->create(['key' => $option['key'], 'label' => $option['label'], 'is_correct' => (bool) ($option['is_correct'] ?? false), 'sort_order' => $index + 1]);
            }
        });

        return redirect()->route('teacher.questions.index')->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);
        $question->delete();

        return redirect()->route('teacher.questions.index')->with('status', 'Soal diarsipkan.');
    }

    private function ensureSubjectBelongsToUser(Request $request, int $subjectId): void
    {
        abort_unless(Subject::query()->whereKey($subjectId)->where('institution_id', $request->user()->institution_id)->exists(), 403);
    }
}
