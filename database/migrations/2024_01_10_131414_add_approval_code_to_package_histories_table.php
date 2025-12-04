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
        if (!Schema::hasColumn('package_histories', 'approval_code')) {
            Schema::table('package_histories', function (Blueprint $table) {
                $table->string('approval_code')->nullable()->after('payment_method_identity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('package_histories', 'approval_code')) {
            Schema::table('package_histories', function (Blueprint $table) {
                $table->dropColumn('approval_code');
            });
        }
    }
};
