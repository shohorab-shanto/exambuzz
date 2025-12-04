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
        if (!Schema::hasColumn('package_histories', 'pg_transaction_id')) {
            Schema::table('package_histories', function (Blueprint $table) {
                $table->string('pg_transaction_id')->nullable()->after('transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('package_histories', 'pg_transaction_id')) {
            Schema::table('package_histories', function (Blueprint $table) {
                $table->dropColumn('pg_transaction_id');
            });
        }
    }
};
