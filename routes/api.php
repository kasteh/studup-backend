<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Middleware\FrontendCors;
use App\Http\Middleware\CheckFrontendApiKey;
use App\Http\Controllers\Api\DomaineController;
use App\Http\Controllers\Api\UserController;
use App\Models\Extrauser;

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
    Route::get('/domaines', [DomaineController::class, 'index'])->name('domaines.list');
    Route::get('/disciplines', [DomaineController::class, 'getDisciplines'])->name('disciplines.list');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('users.logout');

        Route::get('/getUser', [UserController::class, 'getUser'])->name('users.get');
        Route::post('/extrauser', [Extrauser::class, 'store'])->name('users.extra.store');
    });
});
