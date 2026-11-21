<?php

namespace App\Notifications;

use App\Models\ExamResult;
use Illuminate\Notifications\Notification;

class ResultPublishedNotification extends Notification
{
    public function __construct(private readonly ExamResult $result) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => 'Hasil ujian tersedia', 'message' => 'Hasil '.$this->result->exam->name.' sudah dipublikasikan.', 'url' => route('student.results.show', $this->result), 'result_id' => $this->result->id];
    }
}
