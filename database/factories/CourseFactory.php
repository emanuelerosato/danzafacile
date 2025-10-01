<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'instructor_id' => User::factory(),
            'name' => fake()->randomElement(['Danza Classica', 'Danza Moderna', 'Hip Hop', 'Jazz']) . ' - ' . fake()->randomElement(['Base', 'Intermedio', 'Avanzato']),
            'description' => fake()->paragraph(),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+2 months', '+6 months'),
            'schedule' => json_encode([
                'day' => fake()->randomElement(['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato']),
                'time' => fake()->time('H:i'),
                'duration' => fake()->numberBetween(60, 120) . ' minuti'
            ]),
            'max_students' => fake()->numberBetween(10, 30),
            'price' => fake()->randomFloat(2, 50, 200),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
