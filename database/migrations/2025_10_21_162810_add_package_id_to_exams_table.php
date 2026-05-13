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
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('topic_id');
                $table->index('package_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'package_id')) {
                $table->dropIndex(['package_id']);
                $table->dropColumn('package_id');
            }
        });
    }
};
