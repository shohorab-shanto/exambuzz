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
        if (!Schema::hasTable('written_answer_review_conversations')) {
            Schema::create('written_answer_review_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('review_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('user_type', ['student', 'teacher', 'admin']);
                $table->text('message');
                $table->timestamps();

                $table->index('review_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_answer_review_conversations');
    }
};
