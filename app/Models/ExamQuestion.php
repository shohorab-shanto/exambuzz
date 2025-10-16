<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model {
    use HasFactory;
    protected $guarded = [];

    public function questionOptions() {
        return $this->hasMany(ExamQuestionOption::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function topic() {
        return $this->belongsTo(TopicSource::class, 'topic_id');
    }
}
