<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => \App\Models\School::factory(),
            'user_id' => \App\Models\User::factory(),
            'employee_id' => 'EMP-' . $this->faker->unique()->numerify('#####'),
            'role' => $this->faker->randomElement(['instructor', 'coordinator', 'admin_assistant', 'receptionist']),
            'department' => $this->faker->randomElement(['dance', 'administration', 'front_desk']),
            'employment_type' => 'full_time',
            'status' => 'active',
            'hire_date' => now(),
            'hourly_rate' => $this->faker->randomFloat(2, 15, 50),
            'monthly_salary' => null,
            'payment_method' => 'bank_transfer',
            'availability' => [],
            'max_hours_per_week' => 40,
            'can_substitute' => false,
        ];
    }
}
