<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionTopicSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_subjects_id',
        'topic',
        'source',
    ];

    public function subject()
    {
        return $this->belongsTo(RevisionSubject::class, 'revision_subjects_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(RevisionTopicQuestion::class, 'revision_topic_source_id', 'id');
    }
}
