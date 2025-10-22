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
        Schema::table('notice_boards', function (Blueprint $table) {
            $table->boolean('send_notification')->default(true)->after('status')->comment('Send push notification to users');
            $table->string('target_audience')->default('all')->after('send_notification')->comment('all, user, teacher');
            $table->boolean('notification_sent')->default(false)->after('target_audience')->comment('Track if notification was sent');
            $table->timestamp('notification_sent_at')->nullable()->after('notification_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notice_boards', function (Blueprint $table) {
            $table->dropColumn(['send_notification', 'target_audience', 'notification_sent', 'notification_sent_at']);
        });
    }
};
