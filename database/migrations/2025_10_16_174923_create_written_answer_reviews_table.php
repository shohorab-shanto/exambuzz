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
        Schema::create('written_answer_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('written_answer_id')->comment('Which answer sheet is being reviewed');
            $table->unsignedBigInteger('user_id')->comment('Student who gave the review');
            $table->unsignedBigInteger('teacher_id')->comment('Teacher who was reviewed');
            $table->tinyInteger('rating')->comment('1-5 star rating');
            $table->text('comment')->comment('Student review/comment');
            $table->text('teacher_reply')->nullable()->comment('Teacher response to review');
            $table->timestamp('replied_at')->nullable()->comment('When teacher replied');
            $table->timestamps();

            // Indexes for better performance
            $table->index('written_answer_id');
            $table->index('teacher_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_reviews');
    }
};
