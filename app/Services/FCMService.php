<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FCMService
{
    protected static function getAccessToken()
    {
        $jsonPath = config('fcm.credentials');

        if (!file_exists(base_path($jsonPath))) {
            Log::error('FCM: Credential JSON not found', ['path' => $jsonPath]);
            return null;
        }

        $client = new Client();
        $client->setAuthConfig(base_path($jsonPath));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'] ?? null;
    }

    public static function send($deviceToken, $arg2, $arg3 = null, $arg4 = [])
    {
        if (!config('fcm.enabled')) {
            return true;
        }

        $title = is_array($arg2) ? (string)($arg2['title'] ?? '') : (string)$arg2;
        $body = is_array($arg2) ? (string)($arg2['body'] ?? '') : (string)$arg3;
        $data = is_array($arg2) ? (is_array($arg3) ? $arg3 : []) : (is_array($arg4) ? $arg4 : []);

        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::error('FCM: Unable to fetch access token');
            return false;
        }

        $projectId = config('fcm.project_id');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ],
        ];

        if (!empty($data)) {
            $map = [];
            foreach ($data as $k => $v) {
                if (is_string($k) || is_int($k)) {
                    $map[(string)$k] = is_scalar($v) ? (string)$v : json_encode($v);
                }
            }
            if (!empty($map)) {
                $payload['message']['data'] = $map;
            }
        }

        $response = Http::withToken($accessToken)
            ->post($url, $payload);

        if ($response->successful()) {
            return true;
        }

        Log::error('FCM v1 Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return false;
    }
}