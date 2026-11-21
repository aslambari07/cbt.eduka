<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExamPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => 'Ujian baru tersedia', 'message' => $this->exam->name.' sudah dapat dikerjakan.', 'url' => route('student.exams.show', $this->exam), 'exam_id' => $this->exam->id];
    }

    public function viaQueues(): array
    {
        return ['database' => 'notifications'];
    }
}
