<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Firebase service account credentials for authentication
    |
    */

    // Path to Firebase service account JSON file
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase-credentials.json')),

    // Firebase project ID
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    // Firebase database URL (optional)
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    // Enable/disable Firebase authentication
    'enabled' => env('FIREBASE_ENABLED', true),
];

