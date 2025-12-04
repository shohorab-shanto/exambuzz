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
        if (!Schema::hasTable('written_answer_reviews')) {
            Schema::create('written_answer_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('written_answer_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('teacher_id');
                $table->tinyInteger('rating');
                $table->text('comment');
                $table->text('teacher_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();

                $table->index('written_answer_id');
                $table->index('teacher_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_reviews');
    }
};
