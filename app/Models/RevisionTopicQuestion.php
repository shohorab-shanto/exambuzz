<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionTopicQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_subjects_id',
        'revision_topic_source_id',
        'question_name',
        'question_explanation',
        'correct',
        'negative',
        'empty',
        'total',
    ];

    public function questionOptions()
    {
        return $this->hasMany(RevisionTopicQuestionOption::class, 'revision_topic_question_id', 'id');
    }

    public function topicSource()
    {
        return $this->belongsTo(RevisionTopicSource::class, 'revision_topic_sources_id', 'id');
    }

    // isRead is a boolean field
    public function isRead()
    {
        return $this->hasMany(RevisionRead::class, 'revision_topic_question_id', 'id')->where('is_read', 1);
    }

    // is favorite
    public function isFavorite()
    {
        return $this->hasMany(RevisionFavorite::class, 'revision_topic_question_id', 'id')->where('is_favorite', 1);
    }

}
