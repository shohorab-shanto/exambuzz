<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revision_topic_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revision_subjects_id');
            $table->unsignedBigInteger('revision_topic_source_id');
            $table->longText('question_name')->nullable();
            $table->longText('question_explanation')->nullable();
            $table->unsignedBigInteger('correct')->default(0);
            $table->unsignedBigInteger('negative')->default(0);
            $table->unsignedBigInteger('empty')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_topic_questions');
    }
};
