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
        Schema::create('preliminary_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_id')->index();
            $table->string('type')->comment('OMR or Live');
            $table->text('answer');
            $table->unsignedInteger('positive_count')->default(0);
            $table->unsignedInteger('negative_count')->default(0);
            $table->unsignedInteger('empty_count')->default(0);
            $table->decimal('positive_marks',8,2)->default(0);
            $table->decimal('negative_marks',8,2)->default(0);
            $table->decimal('empty_marks',8,2)->default(0);
            $table->decimal('obtained_marks',8,2)->default(0);
            $table->tinyInteger('result_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preliminary_answers');
    }
};
