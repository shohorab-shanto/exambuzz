<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TopicSource;
use App\Models\ExamQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OthersTopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating "Others" topics for each subject...');

        // Get all subjects
        $subjects = Subject::all();

        foreach ($subjects as $subject) {
            // Check if "Others" topic already exists for this subject
            $othersTopic = TopicSource::where('subject_id', $subject->id)
                ->where('topic', 'Others')
                ->first();

            if (!$othersTopic) {
                // Create "Others" topic for this subject
                $othersTopic = TopicSource::create([
                    'subject_id' => $subject->id,
                    'topic' => 'Others',
                    'source' => 'Others'
                ]);

                $this->command->info("Created 'Others' topic (ID: {$othersTopic->id}) for subject: {$subject->name}");
            } else {
                $this->command->info("'Others' topic already exists (ID: {$othersTopic->id}) for subject: {$subject->name}");
            }

            // Update all exam questions for this subject where topic_id is NULL
            $updatedCount = ExamQuestion::where('subject_id', $subject->id)
                ->whereNull('topic_id')
                ->update(['topic_id' => $othersTopic->id]);

            if ($updatedCount > 0) {
                $this->command->info("Updated {$updatedCount} questions to use 'Others' topic for subject: {$subject->name}");
            }
        }

        $this->command->info('✓ Seeding completed successfully!');
    }
}
