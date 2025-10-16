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
        Schema::create('written_answer_review_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id')->comment('Links to written_answer_reviews table');
            $table->unsignedBigInteger('user_id')->comment('Who posted this message');
            $table->enum('user_type', ['student', 'teacher', 'admin'])->comment('Role of the person posting');
            $table->text('message')->comment('The conversation message');
            $table->timestamps();

            // Indexes
            $table->index('review_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_review_conversations');
    }
};
