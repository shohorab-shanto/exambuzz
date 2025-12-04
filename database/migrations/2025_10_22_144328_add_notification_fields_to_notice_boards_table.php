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
            if (!Schema::hasColumn('notice_boards', 'send_notification')) {
                $table->boolean('send_notification')->default(true)->after('status');
            }
            if (!Schema::hasColumn('notice_boards', 'target_audience')) {
                $table->string('target_audience')->default('all')->after('send_notification');
            }
            if (!Schema::hasColumn('notice_boards', 'notification_sent')) {
                $table->boolean('notification_sent')->default(false)->after('target_audience');
            }
            if (!Schema::hasColumn('notice_boards', 'notification_sent_at')) {
                $table->timestamp('notification_sent_at')->nullable()->after('notification_sent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notice_boards', function (Blueprint $table) {
            foreach (['send_notification', 'target_audience', 'notification_sent', 'notification_sent_at'] as $col) {
                if (Schema::hasColumn('notice_boards', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
