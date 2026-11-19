<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->randomElement(['Matematika', 'Biologi', 'Bahasa Indonesia']),
            'code' => strtoupper(fake()->unique()->bothify('SUB###')),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
