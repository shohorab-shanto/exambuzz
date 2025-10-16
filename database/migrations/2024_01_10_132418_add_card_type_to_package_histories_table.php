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
        Schema::table('package_histories', function (Blueprint $table) {
            $table->string('card_type')->nullable()->after('payment_method_identity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->dropColumn('card_type');
        });
    }
};
