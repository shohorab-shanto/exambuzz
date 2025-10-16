<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WrittenAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function written()
    {
        return $this->belongsTo(Written::class);
    }

    public function writtenAnswerQuestion()
    {
        return $this->hasMany(WrittenAnswerQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}
