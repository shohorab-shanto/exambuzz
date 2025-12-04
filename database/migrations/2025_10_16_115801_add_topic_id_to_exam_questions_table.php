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
        if (!Schema::hasColumn('exam_questions', 'topic_id')) {
            Schema::table('exam_questions', function (Blueprint $table) {
                $table->unsignedBigInteger('topic_id')->nullable()->after('subject_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('exam_questions', 'topic_id')) {
            Schema::table('exam_questions', function (Blueprint $table) {
                $table->dropColumn('topic_id');
            });
        }
    }
};
