<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoutine extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'pdf_file',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get routine by type
     */
    public static function getByType($type)
    {
        return self::where('type', $type)->where('status', 1)->first();
    }

    /**
     * Get full PDF URL
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_file) {
            return asset('storage/' . $this->pdf_file);
        }
        return null;
    }
}
