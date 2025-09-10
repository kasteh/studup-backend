<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Middleware\FrontendCors;
use App\Http\Middleware\CheckFrontendApiKey;


Route::group([
    'middleware' => [
        FrontendCors::class,
        CheckFrontendApiKey::class,
        'throttle:100,1'
    ]
], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('users.register')->middleware('throttle:100,1');
    Route::post('/send-verification-code', [VerificationController::class, 'sendVerificationCode']);
    Route::post('/verify-code', [VerificationController::class, 'verifyCode']);
    Route::post('/login', [AuthController::class, 'login'])->name('users.login');

    Route::middleware('auth:sanctum')->group(function () {
        //
    });
});
