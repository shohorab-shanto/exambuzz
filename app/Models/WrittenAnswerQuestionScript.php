<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenAnswerQuestionScript extends Model {
    use HasFactory;
    protected $guarded = [];

    public function writtenAnswerQuestion() {
        return $this->belongsTo(WrittenAnswerQuestion::class);
    }
}
