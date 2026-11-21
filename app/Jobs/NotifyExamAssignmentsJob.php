<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Notifications\ExamPublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyExamAssignmentsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(public readonly int $examId) {}

    public function uniqueId(): string
    {
        return 'exam-published-'.$this->examId;
    }

    public function handle(): void
    {
        $exam = Exam::query()->find($this->examId);
        if (! $exam) {
            return;
        }

        $exam->assignments()->with('student')->chunkById(200, function ($assignments) use ($exam): void {
            foreach ($assignments as $assignment) {
                $assignment->student?->notify((new ExamPublishedNotification($exam))->afterCommit());
            }
        });
    }
}
