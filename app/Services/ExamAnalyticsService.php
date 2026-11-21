<?php

namespace App\Services;

use App\Enums\ExamResultStatus;
use App\Models\Exam;
use App\Models\ExamResult;

class ExamAnalyticsService
{
    public function summarize(Exam $exam): array
    {
        $results = ExamResult::query()->where('exam_id', $exam->id)->whereIn('status', [ExamResultStatus::GRADED->value, ExamResultStatus::PUBLISHED->value])->with('details.question')->get();
        $scores = $results->pluck('score')->map(fn ($score) => (float) $score);
        $assignments = $exam->assignments()->count();
        $questionStats = $results->flatMap->details->groupBy('question_id')->map(function ($details): array {
            $max = (float) $details->sum('max_points');
            $earned = (float) $details->sum('earned_points');
            $first = $details->first();

            return [
                'question_id' => $first->question_id,
                'prompt' => $first->question?->prompt,
                'accuracy' => $max > 0 ? round($earned / $max * 100, 2) : 0.0,
                'attempts' => $details->count(),
            ];
        })->values()->all();

        return [
            'total_results' => $results->count(),
            'average_score' => round($scores->avg() ?? 0, 2),
            'highest_score' => round($scores->max() ?? 0, 2),
            'lowest_score' => round($scores->min() ?? 0, 2),
            'pass_rate' => $results->count() > 0 ? round($results->where('is_passed', true)->count() / $results->count() * 100, 2) : 0.0,
            'completion_rate' => $assignments > 0 ? round($results->count() / $assignments * 100, 2) : 0.0,
            'question_accuracy' => $questionStats,
        ];
    }
}
