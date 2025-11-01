<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Firebase Web API Key for authentication
    | Get this from Firebase Console > Project Settings > General > Web API Key
    |
    */

    // Firebase Web API Key (simpler approach - no JSON file needed)
    'web_api_key' => env('FIREBASE_WEB_API_KEY', ''),

    // Firebase project ID
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    // Enable/disable Firebase authentication
    'enabled' => env('FIREBASE_ENABLED', true),
];

