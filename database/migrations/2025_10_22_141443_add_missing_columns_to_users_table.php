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
            $table->string('fcm_token')->nullable()->after('remember_token');
            $table->string('type')->default('user')->after('fcm_token'); // user, teacher, admin
            $table->tinyInteger('status')->default(1)->after('type'); // 0 = inactive, 1 = active
            $table->string('registration_id')->unique()->nullable()->after('status');
            $table->unsignedInteger('register_number')->nullable()->after('registration_id');
            $table->string('otp')->nullable()->after('register_number');
            $table->decimal('amount', 10, 2)->nullable()->after('otp');
            $table->text('permission')->nullable()->after('amount'); // JSON field for permissions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fcm_token',
                'type',
                'status',
                'registration_id',
                'register_number',
                'otp',
                'amount',
                'permission'
            ]);
        });
    }
};
