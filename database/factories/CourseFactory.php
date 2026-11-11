<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Course> */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->unique()->bothify('???-###')),
            'capacity' => fake()->numberBetween(5, 20),
            'starts_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
        ];
    }
}
