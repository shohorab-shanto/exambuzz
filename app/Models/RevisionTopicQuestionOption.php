<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionTopicQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_topic_question_id',
        'option',
        'is_answer',
    ];
}
