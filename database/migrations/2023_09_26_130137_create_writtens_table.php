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
        Schema::create('writtens', function (Blueprint $table) {
            $table->id();
            $table->string('subject_id');
            $table->string('topic_id');
            $table->string('category');
            $table->string('subcategory');
            $table->string('childcategory')->nullable();
            $table->timestamp('published_at');
            $table->timestamp('expired_at');
            $table->unsignedBigInteger('duration');
            $table->string('question')->nullable();
            $table->string('answer')->nullable();
            $table->boolean('result_published')->nullable();
            $table->boolean('paper_published')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writtens');
    }
};
