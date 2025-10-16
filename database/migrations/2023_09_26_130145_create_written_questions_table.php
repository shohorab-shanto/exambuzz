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
        Schema::create('written_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('written_id');
            $table->unsignedBigInteger('subject_id');
            $table->text('name');
            $table->unsignedInteger('mark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_questions');
    }
};
