<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForgotPasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|unique:users|email',
                'phone' => 'required|unique:users',
                'password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return $this->validationMessage($validator->errors());
            }

            $last_user = User::where('type', 'user')->latest()->first();

            if ($last_user) {
                $register_number = str_pad((int)$last_user->register_number + 1, 6, "0", STR_PAD_LEFT);
                $registration_number = 1 + $last_user->register_number;
            } else {
                $register_number = str_pad((int)1, 6, "0", STR_PAD_LEFT);
                $registration_number = 1;
            }

            $otp = rand(111111, 999999);
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'registration_id' => date("Y") . $register_number,
                'register_number' => $registration_number,
                'status' => 0,
                'otp' => $otp,
            ]);

            DB::table('forgot_password_otps')->insert([
                'phone' => $user->phone,
                'otp' => $user->otp,
            ]);

            sendSMS($user->phone, $user->otp);
            DB::commit();

            return $this->successMessage('Your account created');

        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->errorMessage($th);
        }

    }

    public function verifyOtp(Request $request)
    {

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'otp' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->validationMessage($validator->errors());
            }

            $otp = DB::table('forgot_password_otps')
                ->where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->first();

            if (!$otp) {
                return $this->errorMessage('Invalid phone or OTP!!', $request->otp);
            }

            DB::table('forgot_password_otps')->where('phone', $request->phone)->delete();

            $user = User::where('phone', $request->phone)->first();
            $user->email_verified_at = now();
            $user->status = 1;
            $user->save();

            DB::commit();

            return $this->successMessage('Your phone number verified successfully!!', '');

        } catch (\Throwable $th) {

            DB::rollBack();

            return $this->errorMessage('Something went wrong!!', '');
        }

    }

    public function resendOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {

            return $this->validationMessage($validator->errors());
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->successMessage('Invalid accoutn!');
        }

        $otp = rand(111111, 999999);
        ForgotPasswordOtp::create([
            'otp' => $otp,
            'phone' => $request->phone,
        ]);

        sendSMS($request->phone, $otp);

        return $this->successMessage('An 6 digit code has been sent to your email!');

    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return $this->validationMessage($validator->errors());
            }

            if (!filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {
                $unverified = User::where('phone', $request->email_or_phone)->whereNull('email_verified_at')->first();

                if ($unverified) {
                    return $this->errorMessage('Unverified account', [
                        'is_verified' => false,
                        'user' => $unverified,
                    ]);
                }

                if (!Auth::attempt([
                    'phone' => $request->email_or_phone,
                    'password' => $request->password,
                    'status' => 1,
                ])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid phone number or unauthorized or inactive account!!',
                    ]);
                }

                $user = Auth::user();

                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ]);

            } else {
                $unverified = User::where('email', $request->email_or_phone)->whereNull('email_verified_at')->first();

                if ($unverified) {
                    return $this->errorMessage('Unverified account', [
                        'is_verified' => false,
                        'user' => $unverified,
                    ]);
                }

                $find_user = User::where('email', $request->email_or_phone)->first();


                if (!isset($find_user)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid email or unauthorized or inactive account!!',
                    ]);
                }

                if ($find_user->google_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please go to forgot password and reset your password!!',
                    ]);
                }

                if ($find_user->facebook_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please continue with facebook or reset password!!',
                    ]);
                }

                if (!Auth::attempt([
                    'email' => $request->email_or_phone,
                    'password' => $request->password,
                    'status' => 1,
                ])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid email or unauthorized or inactive account!!',
                    ]);
                }

                $user = Auth::user();

                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ]);

            }

        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Error in Login',
            ]);
        }

    }

    public function storeForgotPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {

            return $this->validationMessage($validator->errors());
        }

        $otp = rand(111111, 999999);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->errorMessage('This phone is no longer with our records!!');
        }

        ForgotPasswordOtp::create([
            'otp' => $otp,
            'phone' => $request->phone,
        ]);

        sendSMS($request->phone, $otp);

        return $this->successMessage('An 6 digit code has been sent to your phone!');

    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'phone' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {

            return $this->validationMessage($validator->errors());
        }

        $password = DB::table('forgot_password_otps')
            ->where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->first();

        if (!$password) {
            return $this->errorMessage('Something went wrong');
        }

        $user = User::orWhere('phone', $request->phone)->first();

        if ($user && $password) {
            $user->update(['password' => bcrypt($request->password)]);
            $user->save();

            $password = ForgotPasswordOtp::where('phone', $request->phone)->delete();

            return $this->successMessage('New password reset successfully!!');
        } else {
            return $this->errorMessage('The phone is no longer our record!!');
        }

    }

}
