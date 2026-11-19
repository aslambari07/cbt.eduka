<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' School',
            'code' => strtoupper(fake()->unique()->bothify('SCH###')),
            'contact_email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'status' => 'active',
        ];
    }
}
