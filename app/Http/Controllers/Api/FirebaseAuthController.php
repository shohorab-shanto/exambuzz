<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Exception;

class FirebaseAuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        try {
            // Initialize Firebase
            $credentialsPath = config('firebase.credentials');
            
            if (file_exists($credentialsPath)) {
                $factory = (new Factory)->withServiceAccount($credentialsPath);
                $this->auth = $factory->createAuth();
            }
        } catch (Exception $e) {
            \Log::error('Firebase initialization error: ' . $e->getMessage());
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
            // Verify Firebase ID token
            if (!$this->auth) {
                return response()->json([
                    'status' => false,
                    'message' => 'Firebase not configured. Please contact administrator.'
                ], 500);
            }

            $verifiedIdToken = $this->auth->verifyIdToken($request->firebase_token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = $this->auth->getUser($firebaseUid);

            // Extract user data from Firebase token
            $email = $firebaseUser->email ?? null;
            $name = $firebaseUser->displayName ?? 'User';
            $photoUrl = $firebaseUser->photoUrl ?? null;
            
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

        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Firebase token',
                'error' => $e->getMessage()
            ], 401);
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
            // Verify Firebase ID token
            if (!$this->auth) {
                return response()->json([
                    'status' => false,
                    'message' => 'Firebase not configured. Please contact administrator.'
                ], 500);
            }

            $verifiedIdToken = $this->auth->verifyIdToken($request->firebase_token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = $this->auth->getUser($firebaseUid);

            // Extract user data from Firebase
            $email = $firebaseUser->email ?? null;
            $name = $firebaseUser->displayName ?? 'User';
            $photoUrl = $firebaseUser->photoUrl ?? null;
            
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

        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Firebase token',
                'error' => $e->getMessage()
            ], 401);
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
