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
        Schema::table('written_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->comment('Teacher who evaluated this answer');
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('written_answers', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
