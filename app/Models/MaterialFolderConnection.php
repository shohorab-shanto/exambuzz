<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialFolderConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_folders_id',
        'materials_id',
    ];
}
