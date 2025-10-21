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
        Schema::table('writtens', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('topic_id')->comment('Package this written exam belongs to');
            $table->index('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('writtens', function (Blueprint $table) {
            $table->dropIndex(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
