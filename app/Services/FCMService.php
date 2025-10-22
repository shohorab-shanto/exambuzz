<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FCMService {
    /**
     * Send push notification via Firebase Cloud Messaging
     * 
     * @param string $token FCM device token
     * @param array $notification Notification data ['title' => '', 'body' => '']
     * @param array $data Additional data payload (optional)
     * @return bool Success status
     */
    public static function send($token, $notification, $data = []) {
        try {
            // Validate inputs
            if (empty($token)) {
                Log::warning('FCMService: Empty FCM token provided');
                return false;
            }

            if (empty($notification['title']) || empty($notification['body'])) {
                Log::warning('FCMService: Invalid notification data', ['notification' => $notification]);
                return false;
            }

            // Prepare payload
            $payload = [
                'to' => $token,
                'notification' => $notification,
            ];

            // Add data payload if provided
            if (!empty($data)) {
                $payload['data'] = $data;
            }

            // Send request to FCM
            $response = Http::acceptJson()
                ->withToken(config('fcm.token'))
                ->timeout(10)
                ->post('https://fcm.googleapis.com/fcm/send', $payload);

            // Check response
            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['success']) && $result['success'] > 0) {
                    Log::info('FCMService: Notification sent successfully', [
                        'token' => substr($token, 0, 20) . '...',
                        'title' => $notification['title']
                    ]);
                    return true;
                } else {
                    Log::warning('FCMService: FCM returned failure', [
                        'response' => $result,
                        'token' => substr($token, 0, 20) . '...'
                    ]);
                    return false;
                }
            } else {
                Log::error('FCMService: HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

        } catch (Exception $e) {
            Log::error('FCMService: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple tokens
     * 
     * @param array $tokens Array of FCM device tokens
     * @param array $notification Notification data
     * @param array $data Additional data payload (optional)
     * @return array ['success' => count, 'failed' => count]
     */
    public static function sendToMultiple($tokens, $notification, $data = []) {
        $success = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            if (self::send($token, $notification, $data)) {
                $success++;
            } else {
                $failed++;
            }
        }

        Log::info('FCMService: Batch notification completed', [
            'total' => count($tokens),
            'success' => $success,
            'failed' => $failed
        ]);

        return ['success' => $success, 'failed' => $failed];
    }
}
