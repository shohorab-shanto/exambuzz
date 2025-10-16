<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model {
    use HasFactory;
    protected $guarded = [];

    public function preliQuestion() {
        return $this->belongsTo(ExamQuestion::class, 'question_id', 'id');
    }

    public function writtenQuestion() {
        return $this->belongsTo(WrittenQuestion::class, 'question_id', 'id');
    }
}
