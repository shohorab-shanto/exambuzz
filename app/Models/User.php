<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function wallet() {
        return $this->hasOne(TeacherWallet::class, 'user_id', 'id');
    }

    public function assesment() {
        return $this->hasMany(WrittenAnswer::class, 'teacher_id', 'id');
    }

    public function packageHistory() {
        return $this->hasMany(PackageHistory::class);
    }

    // written answer
    public function writtenAnswer() {
        return $this->hasMany(WrittenAnswer::class, 'user_id', 'id');
    }

    // Preliminary exam
    public function preliminaryAnswer() {
        return $this->hasMany(PreliminaryAnswer::class, 'user_id', 'id');
    }

    // Teacher reviews (reviews received by this teacher)
    public function teacherReviews() {
        return $this->hasMany(WrittenAnswerReview::class, 'teacher_id', 'id');
    }

    // Student reviews (reviews given by this student)
    public function studentReviews() {
        return $this->hasMany(WrittenAnswerReview::class, 'user_id', 'id');
    }

    // Get average rating for teacher
    public function getAverageRatingAttribute() {
        if ($this->type !== 'teacher') {
            return null;
        }
        
        return $this->teacherReviews()->avg('rating') ?? 0;
    }

    // Get total reviews count for teacher
    public function getTotalReviewsAttribute() {
        if ($this->type !== 'teacher') {
            return 0;
        }
        
        return $this->teacherReviews()->count();
    }

    // Append these attributes to JSON/Array output
    protected $appends = [];
}
