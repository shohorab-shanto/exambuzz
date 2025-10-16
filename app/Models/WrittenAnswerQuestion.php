<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenAnswerQuestion extends Model {
    use HasFactory;
    protected $guarded = [];
    public function writtenAnswer() {
        return $this->belongsTo(WrittenAnswer::class);
    }

    public function writtenAnswerQuestion() {
        return $this->belongsTo(WrittenQuestion::class, 'written_question_id', 'id');
    }

    public function writtenQuestion() {
        return $this->belongsTo(WrittenQuestion::class, 'written_question_id', 'id');
    }

    public function writtenAnswerQuestionScript() {
        return $this->hasMany(WrittenAnswerQuestionScript::class);
    }
}
