<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model {
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $casts   = [
        'permission' => 'array', // Will convarted to (Array)
        // published_at
        'published_at' => 'date', // Will convarted to (Date)
    ];

    public function getPackageTypeAttribute() {

        if ($this->type == 1) {
            return 'Course Base(Package without any limited exam)';
        } else {
            return 'Exam Base(Package with limited exam)';
        }

    }

    public function packageHistory() {
        return $this->hasMany(PackageHistory::class);
    }

    // enroll students count
    public function enrollStudentsCount() {
        return $this->hasMany(PackageHistory::class);
    }

    // Exams belonging to this package
    public function exams() {
        return $this->hasMany(Exam::class);
    }

    // Written exams belonging to this package
    public function writtens() {
        return $this->hasMany(Written::class);
    }

}
