<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{
    public function facebook_login(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'facebook_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $last_user = User::where('type', 'user')->latest()->first();

        if ($last_user) {
            $register_number = str_pad((int)$last_user->register_number + 1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1 + $last_user->register_number;
        } else {
            $register_number = str_pad((int)1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1;
        }

        $finduser = User::where('facebook_id', $request->facebook_id)->first();

        if (isset($finduser)) {
            $tokenResult = $finduser->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'token_type' => 'Bearer',
                'access_token' => $tokenResult,
                'user' => $finduser,
            ]);

        } else {
            $otp = rand(111111, 999999);

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'registration_id' => date("Y") . $register_number,
                'register_number' => $registration_number,
                'status' => 1,
                'otp' => $otp,
                'facebook_id' => $request->facebook_id,
            ]);

            if ($user->phone){
                DB::table('forgot_password_otps')->insert([
                    'phone' => $user->phone,
                    'otp' => $user->otp,
                ]);
            }

            $finduser = User::where('facebook_id', $request->facebook_id)->first();

            $tokenResult = $finduser->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'token_type' => 'Bearer',
                'access_token' => $tokenResult,
                'user' => $finduser,
            ]);
        }

    }

    // <iframe src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2Fcadzzzgaming%2Fvideos%2F6723163934440048%2F&show_text=false&width=476&t=0" width="476" height="476" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
    public function google_login(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'google_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $last_user = User::where('type', 'user')->latest()->first();

        if ($last_user) {
            $register_number = str_pad((int)$last_user->register_number + 1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1 + $last_user->register_number;
        } else {
            $register_number = str_pad((int)1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1;
        }

        $finduser = User::where('google_id', $request->google_id)->first();
        $finduser2 = User::where('email', $request->email)->first();

        if (isset($finduser) || isset($finduser2)) {
            if (isset($finduser)){
                $tokenResult = $finduser->createToken('authToken')->plainTextToken;
            }else {
                $tokenResult = $finduser2->createToken('authToken')->plainTextToken;
            }

            return response()->json([
                'status' => true,
                'token_type' => 'Bearer',
                'access_token' => $tokenResult,
                'user' => $finduser ?? $finduser2,
            ]);

        } else {
            $otp = rand(111111, 999999);

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'registration_id' => date("Y") . $register_number,
                'register_number' => $registration_number,
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'otp' => $otp,
                'google_id' => $request->google_id,
            ]);

            if ($user->phone){
                DB::table('forgot_password_otps')->insert([
                    'phone' => $user->phone,
                    'otp' => $user->otp,
                ]);
            }

            $finduser = User::where('google_id', $request->google_id)->first();

            $tokenResult = $finduser->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'token_type' => 'Bearer',
                'access_token' => $tokenResult,
                'user' => $finduser,
            ]);
        }

    }
}
