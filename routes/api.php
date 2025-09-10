<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\FrontendCors;


Route::group(['middleware' => ['throttle:100,1', FrontendCors::class]], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('users.register')->middleware('throttle:100,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('users.login');
    });
});
