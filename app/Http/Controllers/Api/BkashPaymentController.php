<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BkashPaymentController extends Controller
{
    private $base_url;
    private $username;
    private $password;
    private $app_key;
    private $app_secret;

    public function __construct()
    {
        $this->base_url = config('bkash.sandbox') 
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta' 
            : config('bkash.base_url', 'https://tokenized.pay.bka.sh/v1.2.0-beta');

        $this->username = config('bkash.username');
        $this->password = config('bkash.password');
        $this->app_key = config('bkash.app_key');
        $this->app_secret = config('bkash.app_secret');

        // $this->base_url = env('BKASH_BASE_URL');
        // $this->username = env('BKASH_USERNAME');
        // $this->password = env('BKASH_PASSWORD');
        // $this->app_key = env('BKASH_APP_KEY');
        // $this->app_secret = env('BKASH_APP_SECRET');
        // dd($this->username);
    }

    public function authHeaders()
    {
        // dd($this->grant());
        return array(
            'Content-Type:application/json',
            'Authorization:' . $this->grant(),
            'X-APP-Key:' . $this->app_key
        );
    }

    public function grant(){
        $value = Cache::get('bkash_token'); 
                if ($value){
                return $value;
            }
        $header = array(
            'Content-Type:application/json',
            'username:' . $this->username,
            'password:' . $this->password
        );

        $body_data = array('app_key' => $this->app_key, 'app_secret' => $this->app_secret);

        $response = $this->curlWithBody('/tokenized/checkout/token/grant', $header, 'POST', json_encode($body_data));
        // dd($response->id_token);
        $responseData = json_decode($response);
        // dd($responseData->id_token);

        if (!isset($responseData->id_token)) {
            Log::error('bKash token grant failed', ['response' => $response]);
            return null;
        }
        Cache::put('bkash_token', $responseData->id_token, now()->addMinutes(55));
        return $responseData->id_token;
    }

    public function curlWithBody($url, $header, $method, $body_data)
    {
        $curl = curl_init($this->base_url . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body_data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function getIdTokenFromRefreshToken($refresh_token)
    {
        $header = array(
            'Content-Type:application/json',
            'username:' . $this->username,
            'password:' . $this->password
        );

        $body_data = array('app_key' => $this->app_key, 'app_secret' => $this->app_secret, 'refresh_token' => $refresh_token);

        $response = $this->curlWithBody('/tokenized/checkout/token/refresh', $header, 'POST', json_encode($body_data));

        $idToken = json_decode($response)->id_token;

        return $idToken;
    }

    // public function grant()
    // {
    //     if (!Schema::hasTable('bkash_token')) {
    //         DB::beginTransaction();
    //         Schema::create('bkash_token', function ($table) {
    //             $table->boolean('sandbox_mode')->notNullable();
    //             $table->bigInteger('id_expiry')->notNullable();
    //             $table->string('id_token', 2048)->notNullable();
    //             $table->bigInteger('refresh_expiry')->notNullable();
    //             $table->string('refresh_token', 2048)->notNullable();
    //         });
    //         $insertedRows = DB::table('bkash_token')->insert([
    //             'sandbox_mode' => 1,
    //             'id_expiry' => 0,
    //             'id_token' => 'id_token',
    //             'refresh_expiry' => 0,
    //             'refresh_token' => 'refresh_token',
    //         ]);

    //         $insertedRows = DB::table('bkash_token')->insert([
    //             'sandbox_mode' => 0,
    //             'id_expiry' => 0,
    //             'id_token' => 'id_token',
    //             'refresh_expiry' => 0,
    //             'refresh_token' => 'refresh_token',
    //         ]);
    //     }

    //     // DB::beginTransaction();

    //     $sandbox = config('bkash.sandbox');

    //     $tokenData = DB::table('bkash_token')->where('sandbox_mode', $sandbox)->first();

    //     if ($tokenData) {
    //         $idExpiry = $tokenData->id_expiry;
    //         $idToken = $tokenData->id_token;
    //         $refreshExpiry = $tokenData->refresh_expiry;
    //         $refreshToken = $tokenData->refresh_token;

    //         if ($idExpiry > time()) {
    //             return $idToken;
    //         }
    //         if ($refreshExpiry > time()) {
    //             $idToken = $this->getIdTokenFromRefreshToken($refreshToken);
    //             $updatedRows = DB::table('bkash_token')
    //                 ->where('sandbox_mode', $sandbox)
    //                 ->update([
    //                     'id_expiry' => time() + 3600,
    //                     'id_token' => $idToken,
    //                 ]);

    //             if ($updatedRows > 0) {
    //                 // DB::commit();
    //             }
    //             return $idToken;
    //         }
    //     }

    //     $header = array(
    //         'Content-Type:application/json',
    //         'username:' . $this->username,
    //         'password:' . $this->password
    //     );

    //     $body_data = array('app_key' => $this->app_key, 'app_secret' => $this->app_secret);

    //     $response = $this->curlWithBody('/tokenized/checkout/token/grant', $header, 'POST', json_encode($body_data));

    //     $responseData = json_decode($response);
    //     // dd($header,$body_data,$response);
    //     if (!isset($responseData->id_token)) {
    //         Log::error('bKash token grant failed', ['response' => $response]);
    //         return null;
    //     }

    //     $idToken = $responseData->id_token;

    //     $updatedRows = DB::table('bkash_token')
    //         ->where('sandbox_mode', $sandbox)
    //         ->update([
    //             'id_expiry' => time() + 3600,
    //             'id_token' => $idToken,
    //             'refresh_expiry' => time() + 864000,
    //             'refresh_token' => $responseData->refresh_token,
    //         ]);

    //     if ($updatedRows > 0) {
    //         // DB::commit();
    //     }
    //     // dd($idToken);
    //     return $idToken;
    // }

    /**
     * Create payment for package purchase
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if package exists and is active
        $package = Package::find($request->package_id);
        if (!$package || $package->status != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Package not available for purchase'
            ], 400);
        }

        // Check if user exists
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        try {
            $header = $this->authHeaders();

            // Generate unique invoice number
            $merchantInvoiceNumber = "PKG_" . $request->package_id . "_" . $request->user_id . "_" . time();

            // Store temporary payment data in session/cache
            $paymentData = [
                'package_id' => $request->package_id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'merchant_invoice_number' => $merchantInvoiceNumber,
            ];

            // dd($paymentData);

            // Store in cache for 15 minutes
            cache()->put('bkash_payment_' . $merchantInvoiceNumber, $paymentData, now()->addMinutes(15));
            // dd(url('/api/bkash/callback'));
            $body_data = array(
                'mode' => '0011',
                'payerReference' => $user->phone ?? '01677444438',
                'callbackURL' => url('/bkash-callback'),
                'amount' => $request->amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $merchantInvoiceNumber
            );

            
            $response = $this->curlWithBody('/tokenized/checkout/create', $header, 'POST', json_encode($body_data));
            // dd($response);
            
            $responseData = json_decode($response);

            if (!isset($responseData->bkashURL)) {
                //initiate payment
                // $packageHistory = PackageHistory::create([
                //     'package_id' => $request->package_id,
                //     'user_id' => $request->user_id,
                //     'amount' => $request->amount,
                //     'transaction_id' => null,
                //     'payment_method' => 'bkash',
                //     'payment_method_identity' => '',
                //     'pg_transaction_id' =>  '',
                //     'bank_transaction_id' => '',
                //     'payment_processes' => 'bkash_tokenized',
                //     'approval_code' =>'',
                // ]);

                Log::error('bKash payment creation failed', [
                    'request' => $body_data,
                    'response' => $response
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create payment',
                    'error' => $responseData->errorMessage ?? 'Unknown error'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'bkashURL' => $responseData->bkashURL,
                    'paymentID' => $responseData->paymentID,
                    'merchantInvoiceNumber' => $merchantInvoiceNumber,
                    'amount' => $request->amount,
                    'callbackURL' => url('/api/bkash/callback')
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('bKash payment creation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Payment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute payment after user completes bKash payment
     * 
     * @param string $paymentID
     * @return mixed
     */
    public function executePayment($paymentID)
    {
        $header = $this->authHeaders();

        $body_data = array(
            'paymentID' => $paymentID
        );

        $response = $this->curlWithBody('/tokenized/checkout/execute', $header, 'POST', json_encode($body_data));

        return $response;
    }

    /**
     * Query payment status
     * 
     * @param string $paymentID
     * @return mixed
     */
    public function queryPayment($paymentID)
    {
        $header = $this->authHeaders();

        $body_data = array(
            'paymentID' => $paymentID,
        );

        $response = $this->curlWithBody('/tokenized/checkout/payment/status', $header, 'POST', json_encode($body_data));

        return $response;
    }

    /**
     * Callback handler after bKash payment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $allRequest = $request->all();

        Log::info('bKash callback received', $allRequest);

        if (!isset($allRequest['status']) || $allRequest['status'] != 'success') {
            return response()->json([
                'status' => false,
                'message' => 'Payment failed or cancelled',
                'data' => $allRequest
            ], 400);
        }

        try {
            // Execute payment
            $response = $this->executePayment($allRequest['paymentID']);

            // If execute fails, try query
            if (is_null($response)) {
                sleep(1);
                $response = $this->queryPayment($allRequest['paymentID']);
            }

            $res_array = json_decode($response, true);

            Log::info('bKash payment response', $res_array);

            if (array_key_exists("statusCode", $res_array) && 
                $res_array['statusCode'] == '0000' && 
                array_key_exists("transactionStatus", $res_array) && 
                $res_array['transactionStatus'] == 'Completed') {

                // Get payment data from cache
                $merchantInvoiceNumber = $res_array['merchantInvoiceNumber'];
                $paymentData = cache()->get('bkash_payment_' . $merchantInvoiceNumber);

                if (!$paymentData) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment data not found or expired'
                    ], 400);
                }

                // Create package history
                $packageHistory = PackageHistory::create([
                    'user_id' => $paymentData['user_id'],
                    'package_id' => $paymentData['package_id'],
                    'amount' => $res_array['amount'],
                    'transaction_id' => $res_array['trxID'],
                    'payment_method' => 'bkash',
                    'payment_method_identity' => $res_array['customerMsisdn'] ?? '',
                    'pg_transaction_id' => $res_array['paymentID'] ?? '',
                    'bank_transaction_id' => $res_array['trxID'] ?? '',
                    'payment_processes' => 'bkash_tokenized',
                    'approval_code' => $res_array['merchantInvoiceNumber'] ?? '',
                ]);

                // Clear cache
                cache()->forget('bkash_payment_' . $merchantInvoiceNumber);

                // Get package details
                $package = Package::find($paymentData['package_id']);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment completed successfully',
                    'data' => [
                        'transactionID' => $res_array['trxID'],
                        'paymentID' => $res_array['paymentID'],
                        'amount' => $res_array['amount'],
                        'currency' => $res_array['currency'],
                        'customerMsisdn' => $res_array['customerMsisdn'] ?? '',
                        'package' => [
                            'id' => $package->id,
                            'name' => $package->name,
                            'validity' => $package->validity,
                        ],
                        'package_history_id' => $packageHistory->id
                    ]
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => $res_array['statusMessage'] ?? 'Payment failed',
                'data' => $res_array
            ], 400);

        } catch (\Exception $e) {
            Log::error('bKash callback exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paymentID' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->queryPayment($request->paymentID);
            $res_array = json_decode($response, true);

            return response()->json([
                'status' => true,
                'message' => 'Payment status retrieved',
                'data' => $res_array
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to check payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search transaction by trxID
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trxID' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $header = $this->authHeaders();
            $body_data = array(
                'trxID' => $request->trxID,
            );

            $response = $this->curlWithBody('/tokenized/checkout/general/searchTransaction', $header, 'POST', json_encode($body_data));
            $res_array = json_decode($response, true);

            return response()->json([
                'status' => true,
                'message' => 'Transaction found',
                'data' => $res_array
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to search transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

