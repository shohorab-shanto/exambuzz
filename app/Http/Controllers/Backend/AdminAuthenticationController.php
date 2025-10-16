<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordLink;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminAuthenticationController extends Controller {
    public function login() {
        return view('backend.auth.login');
    }

    public function storeLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        if (
            Auth::guard('admin')->attempt([
                'email'    => $request->email,
                'password' => $request->password,
                'status'   => 1,
            ])
        ) {

            return to_route('dashboard');

        }

        return to_route('login')->withToastError('Invalid Credentitials!!');

    }

    public function forgotPassword() {
        return view('backend.auth.forgot-password');
    }

    public function storeForgotPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all())->withInput();
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return redirect()->back()->withToastError('This email is no longer with our records!!');
        }

        DB::table('password_reset_tokens')->insert([
            'token'      => $request->_token,
            'email'      => $request->email,
            'created_at' => now(),
        ]);
        $url = route('resetPassword', [$request->_token, 'email' => $request->email]);

        Mail::to($request->email)->send(new ResetPasswordLink($url));

        return redirect()->back()->withToastSuccess('We have sent a fresh reset password link to your email!!');
    }

    public function resetPassword(Request $request) {
        return view('backend.auth.reset-password', ['request' => $request]);
    }

    public function storeResetPassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all())->withInput();
        }

        $password = DB::table('password_reset_tokens')->where('email', $request->email)->where('token', $request->token)->first();

        if (!$password) {
            return redirect()->back()->withToastError('Something went wrong, Invalid token or email!!');
        }

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && $password) {
            $admin->update(['password' => bcrypt($request->password)]);

            $password = DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('login')->withToastSuccess('New password reset successfully!!');
        } else {
            return redirect()->back()->withToastError('The email is no longer our record!!');
        }

    }

}
