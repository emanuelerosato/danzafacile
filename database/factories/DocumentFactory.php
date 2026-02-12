<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['medical', 'photo', 'agreement']; // Solo categorie supportate dal DB
        $fileTypes = ['pdf', 'jpg', 'png', 'doc', 'docx'];
        $fileType = fake()->randomElement($fileTypes);

        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'course_id' => null, // Opzionale
            'name' => fake()->sentence(3),
            'file_path' => 'documents/' . fake()->uuid() . '.' . $fileType,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(100000, 5000000), // 100KB - 5MB
            'category' => fake()->randomElement($categories),
            'status' => 'pending',
            'uploaded_at' => now(),
        ];
    }

    /**
     * Indicate that the document is a medical certificate.
     */
    public function medical(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'medical',
            'name' => 'Certificato Medico ' . fake()->year(),
            'file_type' => 'pdf',
        ]);
    }

    /**
     * Indicate that the document is a photo.
     */
    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'photo',
            'name' => 'Foto ' . fake()->word(),
            'file_type' => fake()->randomElement(['jpg', 'jpeg', 'png']),
        ]);
    }

    /**
     * Indicate that the document is an agreement.
     */
    public function agreement(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'agreement',
            'name' => 'Accordo ' . fake()->word(),
            'file_type' => 'pdf',
        ]);
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_APPROVED,
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_REJECTED,
        ]);
    }

    /**
     * Set specific file size.
     */
    public function withSize(int $bytes): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $bytes,
        ]);
    }

    /**
     * Associate with specific school.
     */
    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    /**
     * Associate with specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'school_id' => $user->school_id, // Ensure consistency
        ]);
    }
}
