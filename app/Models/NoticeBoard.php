<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'send_notification',
        'target_audience',
        'notification_sent',
        'notification_sent_at',
    ];

    protected $casts = [
        'send_notification' => 'boolean',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];
}
