<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleFacebookCallback()
    {
        try {

            $user = Socialite::driver('facebook')->user();

            $finduser = User::where('facebook_id', $user->id)->first();

            if($finduser){

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
                    'facebook_id'=> $user->id,
                    'password' => Hash::make('facebook_1995')
                ]);

                Auth::login($newUser);

                return redirect('/');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
