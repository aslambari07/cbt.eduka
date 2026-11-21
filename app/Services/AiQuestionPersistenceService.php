<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AiQuestionPersistenceService
{
    public function __construct(
        private readonly AiQuestionValidator $validator,
        private readonly QuestionDefinitionService $definitionService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function store(User $user, array $drafts, ?Request $request = null): int
    {
        if (! $user->can('create', Question::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin membuat soal.');
        }

        $validated = $this->validator->validate(['questions' => $drafts], count($drafts))['questions'];
        $created = DB::transaction(function () use ($user, $validated): int {
            $count = 0;

            foreach ($validated as $draft) {
                $subjectId = $draft['subject_id'] ?? null;

                if (! $subjectId) {
                    throw new InvalidArgumentException('Setiap draft yang disimpan harus memiliki mata pelajaran.');
                }

                $subject = Subject::query()->find($subjectId);

                if (! $subject || (! $user->hasRole('admin') && $subject->institution_id !== $user->institution_id)) {
                    throw new AuthorizationException('Mata pelajaran tidak dapat digunakan oleh akun ini.');
                }

                $question = Question::query()->create([
                    'author_id' => $user->id,
                    'subject_id' => $subject->id,
                    'category_id' => $draft['category_id'] ?? null,
                    'type' => $draft['type'],
                    'prompt' => $draft['prompt'],
                    'explanation' => $draft['explanation'],
                    'definition' => $this->definitionService->build($draft),
                    'points' => $draft['points'],
                    'difficulty' => $draft['difficulty'],
                    'status' => 'draft',
                ]);

                foreach ($draft['options'] as $sortOrder => $option) {
                    $question->options()->create([
                        'key' => $option['key'],
                        'label' => $option['label'],
                        'is_correct' => $option['is_correct'],
                        'sort_order' => $sortOrder + 1,
                    ]);
                }

                $count++;
            }

            return $count;
        });

        $this->auditLogService->record($user, 'ai.questions_saved', null, [
            'count' => $created,
            'status' => 'draft',
        ], $request);

        return $created;
    }
}
