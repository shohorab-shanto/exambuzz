<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenAnswerReviewConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'user_type',
        'message',
    ];

    // User who posted this message
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The review this conversation belongs to
    public function review()
    {
        return $this->belongsTo(WrittenAnswerReview::class, 'review_id');
    }
}
