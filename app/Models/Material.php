<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function folders()
    {
        return $this->hasManyThrough(
            MaterialFolder::class,
            MaterialFolderConnection::class,
            'materials_id', // Foreign key on users table
            'id', // Foreign key on posts table
            'id', // Local key on countries table
            'material_folders_id' // Local key on users table
        );
    }

    public function folder_connection()
    {
        return $this->hasMany(MaterialFolderConnection::class, 'materials_id', 'id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'id', 'subject_id');
    }

    public function sources()
    {
        return $this->hasMany(TopicSource::class, 'id', 'topic_id');
    }
}
