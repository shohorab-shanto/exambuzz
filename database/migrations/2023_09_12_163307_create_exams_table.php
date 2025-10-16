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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('subject_id');
            $table->string('topic_id');
            $table->string('name');
            $table->string('category');
            $table->string('subcategory');
            $table->string('childcategory')->nullable();
            $table->decimal('per_question_positive_mark', 8,2);
            $table->decimal('per_question_negative_mark', 8,2);
            $table->timestamp('published_at');
            $table->timestamp('expired_at');
            $table->unsignedBigInteger('duration');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
