<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionFavorite extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'revision_topic_question_id',
        'is_favorite',
    ];

}
