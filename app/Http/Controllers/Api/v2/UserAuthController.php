<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;

use App\Models\ForgotPasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendOtp;
use Illuminate\Support\Facades\Mail;

class UserAuthController extends Controller
{
    public function storeForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationMessage($validator->errors());
        }

        $input = $request->phone;
        $otp = rand(111111, 999999);

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            // Input is an email
            $user = User::where('email', $input)->first();

            if (!$user) {
                return $this->errorMessage('This email is not in our records!!');
            }

            ForgotPasswordOtp::create([
                'otp' => $otp,
                'phone' => $input,
            ]);

            // send otp mail
            Mail::to($input)->send(new SendOtp($otp));

            return $this->successMessage('A 6-digit code has been sent to your email. Please check your inbox or spam!');
        } else {
            // Assume input is a phone number
            $user = User::where('phone', $input)->first();

            if (!$user) {
                return $this->errorMessage('This phone is not in our records!!');
            }

            ForgotPasswordOtp::create([
                'otp' => $otp,
                'phone' => $input,
            ]);

            sendSMS($input, $otp);

            return $this->successMessage('A 6-digit code has been sent to your phone!');
        }
    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {

            return $this->validationMessage($validator->errors());
        }

        $password = DB::table('forgot_password_otps')->where('phone', $request->phone)->first();

        if (!$password) {
            return $this->errorMessage('Something went wrong');
        }

        if (filter_var($request->phone, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->phone)->first();
        } else {
            $user = User::where('phone', $request->phone)->first();
        }

        if ($user && $password) {
            $user->update(['password' => bcrypt($request->password)]);
            // google_id
            $user->google_id = null;
            $user->save();

            $password = ForgotPasswordOtp::where('phone', $request->phone)->delete();

            return $this->successMessage('New password reset successfully!!');
        } else {
            return $this->errorMessage('The phone is no longer our record!!');
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

            //DB::table('forgot_password_otps')->where('phone', $request->phone)->delete();

            if (filter_var($request->phone, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $request->phone)->first();
                $verify_type = 'email';
            } else {
                $user = User::where('phone', $request->phone)->first();
                $verify_type = 'phone number';
            }

            $user->email_verified_at = now();
            $user->status = 1;
            $user->save();

            DB::commit();

            return $this->successMessage('Your '.$verify_type.' verified successfully!!', '');

        } catch (\Throwable $th) {

            DB::rollBack();

            return $this->errorMessage('Something went wrong!!', '');
        }

    }
}
