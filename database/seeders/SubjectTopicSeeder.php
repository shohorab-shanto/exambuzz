<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TopicSource;
use Illuminate\Database\Seeder;

class SubjectTopicSeeder extends Seeder
{
    /**
     * Seed demo subjects with their topics.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name'   => 'Mathematics',
                'topics' => [
                    ['topic' => 'Algebra Fundamentals', 'source' => 'Lecture Notes'],
                    ['topic' => 'Geometry & Mensuration', 'source' => 'Textbook'],
                    ['topic' => 'Calculus Essentials', 'source' => 'Practice Set'],
                ],
            ],
            [
                'name'   => 'Physics',
                'topics' => [
                    ['topic' => 'Mechanics', 'source' => 'Lecture Notes'],
                    ['topic' => 'Electricity & Magnetism', 'source' => 'Reference Book'],
                    ['topic' => 'Waves & Optics', 'source' => 'Question Bank'],
                ],
            ],
            [
                'name'   => 'Chemistry',
                'topics' => [
                    ['topic' => 'Physical Chemistry', 'source' => 'Textbook'],
                    ['topic' => 'Organic Chemistry', 'source' => 'Lecture Notes'],
                    ['topic' => 'Inorganic Chemistry', 'source' => 'Practice Set'],
                ],
            ],
            [
                'name'   => 'Biology',
                'topics' => [
                    ['topic' => 'Cell Biology', 'source' => 'Lecture Notes'],
                    ['topic' => 'Genetics', 'source' => 'Reference Book'],
                    ['topic' => 'Human Physiology', 'source' => 'Question Bank'],
                ],
            ],
        ];

        foreach ($subjects as $subjectData) {
            $subject = Subject::updateOrCreate(
                ['name' => $subjectData['name']],
                ['name' => $subjectData['name']]
            );

            foreach ($subjectData['topics'] as $topicData) {
                TopicSource::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'topic'      => $topicData['topic'],
                    ],
                    ['source' => $topicData['source']]
                );
            }
        }
    }
}

