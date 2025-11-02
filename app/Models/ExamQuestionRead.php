<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestionRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_question_id',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examQuestion()
    {
        return $this->belongsTo(ExamQuestion::class);
    }
}
