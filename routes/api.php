<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('users.login');
    });
});
