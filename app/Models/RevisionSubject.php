<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionSubject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function topicAndSources()
    {
        return $this->hasMany(RevisionTopicSource::class, 'revision_subjects_id', 'id');
    }

    public function questions()
    {
       return $this->hasMany(RevisionTopicQuestion::class, 'revision_subjects_id', 'id');
    }

}
