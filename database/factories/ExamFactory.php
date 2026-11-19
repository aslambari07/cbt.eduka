<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exam> */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $institution = Institution::factory();

        return [
            'owner_id' => User::factory(),
            'institution_id' => $institution,
            'subject_id' => Subject::factory()->state(fn (array $attributes) => ['institution_id' => $attributes['institution_id'] ?? null]),
            'name' => fake()->sentence(3),
            'code' => fake()->unique()->bothify('EXAM-####-???'),
            'description' => null,
            'instructions' => null,
            'duration_minutes' => 60,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'total_points' => 0,
            'passing_grade' => 75,
            'max_attempts' => 1,
            'randomize_questions' => false,
            'randomize_options' => false,
            'navigation_mode' => 'free',
            'results_visibility' => 'after_review',
            'status' => ExamStatus::DRAFT,
        ];
    }
}
