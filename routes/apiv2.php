<?php


use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\v2\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->group(function () {

    Route::controller(UserAuthController::class)->prefix('/auth')->group(function () {
        Route::post('/store-forgot-password', 'storeForgotPassword');
        Route::post('/reset-password', 'resetPassword');
        Route::post('/verify-otp', 'verifyOtp');
    });

    Route::get('/get-top-3-student/{written_id}', [AnswerController::class, 'get_top_3_student']);
});
