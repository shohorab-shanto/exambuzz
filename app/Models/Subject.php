<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
    use HasFactory;
    protected $guarded = [];

    public function topicAndSources() {
        return $this->hasMany(TopicSource::class);
    }

    public function exams() {
        return $this->hasMany(ExamQuestion::class);
    }
}
