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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->string('fcm_token')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'type')) {
                $table->string('type')->default('user')->after('fcm_token');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->tinyInteger('status')->default(1)->after('type');
            }
            if (!Schema::hasColumn('users', 'registration_id')) {
                $table->string('registration_id')->unique()->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'register_number')) {
                $table->unsignedInteger('register_number')->nullable()->after('registration_id');
            }
            if (!Schema::hasColumn('users', 'otp')) {
                $table->string('otp')->nullable()->after('register_number');
            }
            if (!Schema::hasColumn('users', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('otp');
            }
            if (!Schema::hasColumn('users', 'permission')) {
                $table->text('permission')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'fcm_token',
                'type',
                'status',
                'registration_id',
                'register_number',
                'otp',
                'amount',
                'permission',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
