<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'status',
        'parent_id',
    ];

    public function materials()
    {
        return $this->hasManyThrough(
            Material::class,
            MaterialFolderConnection::class,
            'material_folders_id', // Foreign key on users table
            'id', // Foreign key on posts table
            'id', // Local key on countries table
            'materials_id' // Local key on users table
        );
    }

    public function parent()
    {
        return $this->belongsTo(MaterialFolder::class, 'parent_id');
    }
    // public function children()
    // {
    //     return $this->hasMany(MaterialFolder::class, 'parent_id');
    // }
    public function children()
    {
        return $this->hasMany(MaterialFolder::class, 'parent_id')->with('children');
    }
}
