<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revision_topic_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revision_topic_question_id');
            $table->text('option');
            $table->tinyInteger('is_answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_topic_question_options');
    }
};
