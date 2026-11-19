<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'type' => QuestionType::MULTIPLE_CHOICE,
            'prompt' => fake()->sentence(),
            'explanation' => fake()->sentence(),
            'definition' => ['correct_option' => 'A'],
            'points' => 2,
            'difficulty' => 'medium',
            'status' => 'draft',
        ];
    }
}
