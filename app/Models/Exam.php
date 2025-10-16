<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Exam extends Model {
    use HasFactory;
    protected $guarded = [];

    public function questions() {
        return $this->hasMany(ExamQuestion::class);
    }

    public function userAnswer() {
        return $this->hasOne(PreliminaryAnswer::class);
    }

    protected $casts = [
        'published_at' => 'datetime: Y-m-d H:i:s',
        'expired_at'   => 'datetime: Y-m-d H:i:s',
    ];
}
