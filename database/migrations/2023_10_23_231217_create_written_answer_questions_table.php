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
        Schema::create('written_answer_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('written_answer_id');
            $table->unsignedBigInteger('written_question_id');
            $table->unsignedDecimal('marks',8,2)->default(0.00);
            $table->text('comment')->nullable();
            $table->tinyInteger('is_checked_by_teacher',0,1)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_questions');
    }
};
