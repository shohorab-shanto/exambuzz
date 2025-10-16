<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WrittenQuestion>
 */
class WrittenQuestionFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'written_id' => 9,
            'subject_id' => fake()->randomElement(['1','2']),
            'name'       => fake()->sentence(12),
            'mark'       => fake()->randomElement(['3', '5']),
        ];
    }
}
