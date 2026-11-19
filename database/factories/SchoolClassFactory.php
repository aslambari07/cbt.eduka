<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->unique()->randomElement(['XII IPA 1', 'XII IPA 2', 'XI IPS 1']),
            'level' => fake()->randomElement(['X', 'XI', 'XII']),
            'status' => 'active',
        ];
    }
}
