<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;

class FirebaseAuthController extends Controller
{
    protected $webApiKey;

    public function __construct()
    {
        $this->webApiKey = config('firebase.web_api_key');
    }

    /**
     * Verify Firebase ID Token using REST API
     */
    private function verifyFirebaseToken($idToken)
    {
        try {
            $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$this->webApiKey}", [
                'idToken' => $idToken
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['users']) && count($data['users']) > 0) {
                    return $data['users'][0];
                }
            }

            return null;
        } catch (Exception $e) {
            \Log::error('Firebase token verification error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Firebase Google Login
     * Mobile app sends ONLY Firebase ID token - that's it!
     */
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firebase_token' => 'required|string',
            'phone' => 'nullable|string', // Optional - can be added later
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if Firebase Web API Key is configured
            if (empty($this->webApiKey)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Firebase not configured. Please add FIREBASE_WEB_API_KEY to .env file.'
                ], 500);
            }

            // Verify Firebase ID token using REST API
            $firebaseUser = $this->verifyFirebaseToken($request->firebase_token);

            if (!$firebaseUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Firebase token',
                ], 401);
            }

            // Extract user data from Firebase response
            $firebaseUid = $firebaseUser['localId'] ?? null;
            $email = $firebaseUser['email'] ?? null;
            $name = $firebaseUser['displayName'] ?? 'User';
            $photoUrl = $firebaseUser['photoUrl'] ?? null;
            $emailVerified = $firebaseUser['emailVerified'] ?? false;
            
            // Phone is completely optional - can be null
            $phone = $request->phone ?? null;

            // Check if user exists by firebase_uid or email
            $user = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Update firebase_uid if not set
                if (!$user->firebase_uid) {
                    $user->firebase_uid = $firebaseUid;
                    $user->save();
                }

                // Update phone if provided and user doesn't have one
                if ($phone && !$user->phone) {
                    $user->phone = $phone;
                    $user->save();
                }

                // Generate access token
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ]);
            } else {
                // Create new user
                $user = $this->createUserFromFirebase([
                    'firebase_uid' => $firebaseUid,
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                    'photo' => $photoUrl,
                    'email_verified' => $emailVerified,
                ]);

                // Generate access token
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Registration successful',
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ], 201);
            }

        } catch (Exception $e) {
            \Log::error('Firebase Google Login Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Firebase Facebook Login
     * Mobile app sends ONLY Firebase ID token
     */
    public function facebookLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firebase_token' => 'required|string',
            'phone' => 'nullable|string', // Optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if Firebase Web API Key is configured
            if (empty($this->webApiKey)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Firebase not configured. Please add FIREBASE_WEB_API_KEY to .env file.'
                ], 500);
            }

            // Verify Firebase ID token using REST API
            $firebaseUser = $this->verifyFirebaseToken($request->firebase_token);

            if (!$firebaseUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Firebase token',
                ], 401);
            }

            // Extract user data from Firebase response
            $firebaseUid = $firebaseUser['localId'] ?? null;
            $email = $firebaseUser['email'] ?? null;
            $name = $firebaseUser['displayName'] ?? 'User';
            $photoUrl = $firebaseUser['photoUrl'] ?? null;
            $emailVerified = $firebaseUser['emailVerified'] ?? false;
            
            // Phone is optional
            $phone = $request->phone ?? null;

            // Check if user exists by firebase_uid or email
            $user = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Update firebase_uid if not set
                if (!$user->firebase_uid) {
                    $user->firebase_uid = $firebaseUid;
                    $user->save();
                }

                // Update phone if provided and user doesn't have one
                if ($phone && !$user->phone) {
                    $user->phone = $phone;
                    $user->save();
                }

                // Generate access token
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ]);
            } else {
                // Create new user
                $user = $this->createUserFromFirebase([
                    'firebase_uid' => $firebaseUid,
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                    'photo' => $photoUrl,
                    'email_verified' => $emailVerified,
                ]);

                // Generate access token
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Registration successful',
                    'token_type' => 'Bearer',
                    'access_token' => $tokenResult,
                    'user' => $user,
                ], 201);
            }

        } catch (Exception $e) {
            \Log::error('Firebase Facebook Login Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to create user from Firebase data
     */
    private function createUserFromFirebase($data)
    {
        // Generate registration number
        $last_user = User::where('type', 'user')->latest()->first();
        if ($last_user) {
            $register_number = str_pad((int)$last_user->register_number + 1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1 + $last_user->register_number;
        } else {
            $register_number = str_pad((int)1, 6, "0", STR_PAD_LEFT);
            $registration_number = 1;
        }

        $otp = rand(111111, 999999);

        // Prepare user data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt(uniqid()), // Random password for Firebase users
            'registration_id' => date("Y") . $register_number,
            'register_number' => $registration_number,
            'email_verified_at' => Carbon::now(), // Auto-verify email for Firebase
            'status' => 1,
            'otp' => $otp,
            'firebase_uid' => $data['firebase_uid'],
            'image' => $data['photo'],
        ];

        // Only add phone if provided (avoid unique constraint issues)
        if (!empty($data['phone'])) {
            $userData['phone'] = $data['phone'];
        }

        $user = User::create($userData);

        // Only insert OTP if phone exists
        if ($user->phone) {
            DB::table('forgot_password_otps')->insert([
                'phone' => $user->phone,
                'otp' => $user->otp,
            ]);
        }

        return $user;
    }
}
