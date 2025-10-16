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
        Schema::create('written_answer_question_scripts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('written_answer_question_id');
            $table->string('student_script');
            $table->string('teacher_script')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_question_scripts');
    }
};
