<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {

        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        try {

            $user = Socialite::driver('google')->user();

            $finduser = User::where('google_id', $user->id)->first();

            if($finduser){

                $success['token'] =  $finduser->createToken('MyAuthApp')->plainTextToken;
                Session::put('token', $success['token']);

                Auth::login($finduser);

                return redirect('/');

            }else{
                $name = $user->name;
                $pieces = explode(' ', $name);
                if (count($pieces) > 1){
                    $last_name = array_pop($pieces);
                    $first_name = preg_replace('/\W\w+\s*(\W*)$/', '$1', $name);
                }else {
                    $first_name = $name;
                    $last_name = '';
                }
                $newUser = User::updateOrCreate(['email' => $user->email],[
                    'slug' => Str::random(9),
                    'main_role_id' => 3,
                    'role_id' => null,
                    'role_type' => 1,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'google_id'=> $user->id,
                    'profile_photo_path'=> $user->avatar,
                    'password' => Hash::make('google_1995')
                ]);

                $success['token'] =  $newUser->createToken('MyAuthApp')->plainTextToken;
                Session::put('token', $success['token']);

                Auth::login($newUser);
                return redirect('/');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
