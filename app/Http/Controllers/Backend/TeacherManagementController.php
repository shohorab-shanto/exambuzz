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

        return view('backend.teacher.profile.show', compact('data'));
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

}
