<?php

namespace Database\Factories;

use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExamQuestion>
 */
class ExamQuestionFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'exam_id'              => 12,
            'subject_id'           => fake()->randomElement(['1', '4', '5', '6']),
            'question_name'        => fake()->sentence(12),
            'question_explanation' => fake()->text(400),
        ];
    }

    public function configure() {
        return $this->afterCreating(function (ExamQuestion $question) {

            for ($i = 0; $i < 4; $i++) {
                ExamQuestionOption::create([
                    'exam_question_id' => $question->id,
                    'option'           => fake()->sentence(5),
                    'is_answer'        => $i == 3 ? 1 : 0,
                ]);
            }

        });
    }

}
