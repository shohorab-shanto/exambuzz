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
            if (!Schema::hasColumn('written_answers', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->index('teacher_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('written_answers', function (Blueprint $table) {
            if (Schema::hasColumn('written_answers', 'teacher_id')) {
                $table->dropIndex(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
        });
    }
};
