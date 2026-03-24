<?php

use App\Http\Controllers\Website\Auth\ForgotPasswordController;
use App\Http\Controllers\Website\Auth\LoginController;
use App\Http\Controllers\Website\Auth\RegisterController;
use App\Http\Controllers\Website\Auth\VerifyController;
use App\Http\Controllers\Website\Account\AccountController;
use App\Http\Controllers\Website\Learn\LearnController;
use App\Http\Controllers\Website\Learn\PracticeController;
use App\Http\Controllers\Website\Learn\TestController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('verify', [VerifyController::class, 'verify']);
Route::post('resend-otp', [VerifyController::class, 'resendOtp']);

Route::prefix('forgot-password')->group(function () {
    Route::post('send', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('reset', [ForgotPasswordController::class, 'resetPassword']);
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Account
    Route::get('profile', [AccountController::class, 'profile']);
    Route::post('profile/update-name', [AccountController::class, 'updateName']);
    Route::delete('profile/clear-progress', [AccountController::class, 'clearProgress']);
    Route::post('logout', [AccountController::class, 'logout']);

    // Learn Module
    Route::prefix('learn')->group(function () {
        Route::get('levels', [LearnController::class, 'levels']);
        Route::post('lessons/{id}/complete', [LearnController::class, 'complete']);
    });

    // Practice Module
    Route::prefix('practice')->group(function () {
        Route::get('levels', [PracticeController::class, 'levels']);
        Route::post('lessons/{id}/complete', [PracticeController::class, 'complete']);
    });

    // Test Module
    Route::prefix('test')->group(function () {
        Route::get('levels', [TestController::class, 'levels']);
        Route::post('words/{id}/complete', [TestController::class, 'complete']);
    });
});
