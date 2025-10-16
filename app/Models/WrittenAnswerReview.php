<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenAnswerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'written_answer_id',
        'user_id',
        'teacher_id',
        'rating',
        'comment',
        'teacher_reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    // Student who wrote the review
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Teacher who was reviewed
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // The answer sheet that was reviewed
    public function writtenAnswer()
    {
        return $this->belongsTo(WrittenAnswer::class, 'written_answer_id');
    }

    // Conversation thread (student and teacher/admin messages)
    public function conversations()
    {
        return $this->hasMany(WrittenAnswerReviewConversation::class, 'review_id')->orderBy('created_at', 'asc');
    }
}
