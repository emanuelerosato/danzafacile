<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'amount' => fake()->randomFloat(2, 50, 500),
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'payment_method' => fake()->randomElement(['paypal', 'contanti', 'bonifico', 'carta']),
            'status' => fake()->randomElement(['completed', 'pending', 'failed', 'refunded']),
            'transaction_id' => 'TXN' . fake()->unique()->numerify('########'),
            'receipt_number' => 'REC' . fake()->unique()->numerify('######'),
            'reference_number' => 'REF' . fake()->unique()->numerify('######'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
