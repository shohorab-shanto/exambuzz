<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class TeacherManagementController extends Controller {
    public function index() {
        $data = User::where('type', 'teacher')->with('wallet.teacherWalletHistory')->withCount(['assesment' => function ($q) {
            $q->where('is_checked', 1);
        },
        ])->get();
        // dd($data);

        return view('backend.teacher.profile.index', compact('data'));
    }

    public function createOrEdit($id = null) {

        if ($id) {
            $data = User::find($id);
        } else {
            $data = [];
        }

        return view('backend.teacher.profile.create-or-edit', compact('data'));
    }

    public function storeOrUpdate(Request $request, $id = null) {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'phone'      => 'required|numeric',
            'email'      => 'required|email|unique:users,email,' . $id,
            'password'   => 'nullable|min:8',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'amount'     => 'required|numeric',
            'permission' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all())->withInput();
        }

        if ($id) {
            $user = User::find($id);

            if ($request->hasFile('image')) {

                $image_file = $request->file('image');

                if ($image_file) {

                    $image_path = public_path($user->image);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $img_gen   = hexdec(uniqid());
                    $image_url = 'images/teacher/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name    = $img_gen . '.' . $image_ext;
                    $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                    $user->image = $final_name1;
                    $user->save();

                }

            }

            if ($request->password) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            $user->name       = $request->name;
            $user->email      = $request->email;
            $user->phone      = $request->phone;
            $user->address    = $request->address;
            $user->about      = $request->about;
            $user->amount     = $request->amount;
            $user->status     = $request->status;
            $user->permission = implode(',', $request->permission);
            $user->save();

            return to_route('teacher.index')->withToastSuccess('Teacher updated successfully');
        } else {

            if ($request->hasFile('image')) {

                $image_file = $request->file('image');

                if ($image_file) {

                    $img_gen   = hexdec(uniqid());
                    $image_url = 'images/teacher/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name    = $img_gen . '.' . $image_ext;
                    $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                }

            }

            $last_user = User::where('type', 'teacher')->latest()->first();

            if ($last_user) {
                $register_number     = str_pad((int) $last_user->register_number + 1, 4, "0", STR_PAD_LEFT);
                $registration_number = 1 + $last_user->register_number;
            } else {
                $register_number     = str_pad((int) 1, 4, "0", STR_PAD_LEFT);
                $registration_number = 1;
            }

            User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'password'          => bcrypt($request->password),
                'address'           => $request->address,
                'image'             => $final_name1 ?? null,
                'about'             => $request->about,
                'type'              => 'teacher',
                'amount'            => $request->amount,
                'email_verified_at' => now(),
                'permission'        => implode(',', $request->permission),
                'status'            => $request->status,
                'registration_id'   => date("y") . $register_number,
                'register_number'   => $registration_number,
            ]);

        }

        return to_route('teacher.index')->withToastSuccess('New teacher created successfully');
    }

    public function show($id) {
        $data = User::where('id', $id)->where('type', 'teacher')->withCount(['assesment' => function ($q) {
            $q->where('is_checked', 1);
        },
        ])->first();

        if (!$data) {
            return back();
        }

        // Get rating statistics from written_answer_reviews
        $ratingStats = \DB::table('written_answer_reviews')
            ->where('teacher_id', $id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        $data->avg_rating = $ratingStats->avg_rating ? round($ratingStats->avg_rating, 1) : 0;
        $data->total_reviews = $ratingStats->total_reviews ?? 0;

        return view('backend.teacher.profile.show', compact('data'));
    }

    public function showReviews($id) {
        $teacher = User::where('id', $id)->where('type', 'teacher')->first();

        if (!$teacher) {
            return back()->withToastError('Teacher not found');
        }

        // Get all reviews for this teacher with conversations
        $reviews = \App\Models\WrittenAnswerReview::where('teacher_id', $id)
            ->with([
                'user',
                'teacher',
                'writtenAnswer.written',
                'conversations' => function($query) {
                    $query->orderBy('created_at', 'asc');
                },
                'conversations.user'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('backend.teacher.profile.reviews', compact('teacher', 'reviews'));
    }

    public function addAdminReply(Request $request) {
        $request->validate([
            'review_id' => 'required|exists:written_answer_reviews,id',
            'message' => 'required|string|max:2000',
        ]);

        // Create admin conversation message
        \App\Models\WrittenAnswerReviewConversation::create([
            'review_id' => $request->review_id,
            'user_id' => auth()->id(),
            'user_type' => 'admin',
            'message' => $request->message,
        ]);

        return back()->withToastSuccess('Reply added successfully');
    }

    public function editConversationMessage(Request $request) {
        $request->validate([
            'message_id' => 'required|exists:written_answer_review_conversations,id',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = \App\Models\WrittenAnswerReviewConversation::find($request->message_id);

        // Check if message belongs to current admin
        if ($conversation->user_id != auth()->id()) {
            return back()->withToastError('You can only edit your own messages');
        }

        // Check if message is from admin
        if ($conversation->user_type != 'admin') {
            return back()->withToastError('Only admin messages can be edited');
        }

        $conversation->update([
            'message' => $request->message,
        ]);

        return back()->withToastSuccess('Message updated successfully');
    }

    public function deleteConversationMessage(Request $request) {
        $request->validate([
            'message_id' => 'required|exists:written_answer_review_conversations,id',
        ]);

        $conversation = \App\Models\WrittenAnswerReviewConversation::find($request->message_id);

        // Check if message belongs to current admin
        if ($conversation->user_id != auth()->id()) {
            return back()->withToastError('You can only delete your own messages');
        }

        // Check if message is from admin
        if ($conversation->user_type != 'admin') {
            return back()->withToastError('Only admin messages can be deleted');
        }

        $conversation->delete();

        return back()->withToastSuccess('Message deleted successfully');
    }

    //wallet
    public function withdrawalRequest() {
        $data = WalletHistory::where('status', request()->ref);

        if (request()->ref == 'Paid') {
            $data = $data->orderBy('updated_at', 'desc');
        }

        $data = $data->paginate();

        return view('backend.withdrawal-request', compact('data'));
    }

    public function updateWithdrawalRequest(Request $request, $id) {
        $data = WalletHistory::find($id);

        if ($request->type === 'Paid') {
            $wallet = TeacherWallet::where('id', $data->wallet->id)->first();

            if (!$wallet) {
                return back()->withToastError('Something went wrong');
            } elseif ($wallet->amount < $data->amount) {
                return back()->withToastError('The user has less wallet than requested amount');
            }

            $wallet->amount   = $wallet->amount - $data->amount;
            $wallet->withdraw = $wallet->withdraw + $data->amount;
            $wallet->save();

            $data->payment_method = $request->payment_method;
            $data->note           = $request->note;
        }

        $data->status = $request->type;
        $data->save();

        return back()->withToastSucces('Request marker as ' . $request->type);
    }

    public function delete($id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->image && File::exists(public_path($teacher->image))) {
            File::delete(public_path($teacher->image));
        }
        $teacher->delete();
        return back()->withToastSuccess('Teacher deleted successfully');
    }

}
