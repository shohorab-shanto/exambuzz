<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UserProfileController extends Controller {
    public function user() {
        $user = User::where('id', auth()->user()->id)
            ->withCount([
                'writtenAnswer',
                'writtenAnswer as written_total_passed' => function ($query) {
                    $query->where('result_status', 1);
                },
                'preliminaryAnswer',
                'preliminaryAnswer as preliminary_total_passed' => function ($query) {
                    $query->where('result_status', 1);
                },
                'teacherReviews',
            ])
            ->withAvg('teacherReviews', 'rating')
            ->first();

        if ($user) {
            // Add rating information for teachers
            if ($user->type === 'teacher') {
                $user->average_rating = $user->teacher_reviews_avg_rating 
                    ? number_format($user->teacher_reviews_avg_rating, 1) 
                    : '0.0';
                $user->total_reviews = $user->teacher_reviews_count ?? 0;
                $user->rating_stars = $user->teacher_reviews_avg_rating 
                    ? round($user->teacher_reviews_avg_rating, 1) 
                    : 0;
            }
            
            return $this->successMessage('', $user);
        } else {
            return $this->errorMessage();
        }

    }

    public function update(Request $request) {
        $user = User::find(Auth::id());

        if ($request->hasFile('image')) {

            $image_file = $request->file('image');

            if ($image_file) {

                $image_path = public_path($user->image);

                if (File::exists($image_path)) {
                    File::delete($image_path);
                }

                $img_gen   = hexdec(uniqid());
                $image_url = 'images/user/';
                $image_ext = strtolower($image_file->getClientOriginalExtension());

                $img_name    = $img_gen . '.' . $image_ext;
                $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                $image_file->move($image_url, $img_name);
                $user->image = $final_name1;
                $user->save();

            }

        }

        $user->name  = $request->name;
        $user->about = $request->about;
        $user->save();

        return $this->successMessage();

    }

    public function askingQuery(Request $request) {
        FAQ::create([
            'user_id'  => Auth::id(),
            'question' => $request->question,
        ]);

        return $this->successMessage('Your query submitted successfully');
    }

}
