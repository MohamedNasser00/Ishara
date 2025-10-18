<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\Auth\LoginController;
use App\Http\Controllers\Website\Auth\RegisterController;
use App\Http\Controllers\Website\Auth\VerifyController;
use App\Http\Controllers\Website\Auth\ForgotPasswordController;

// login route for website and dashboard
Route::post('login', [LoginController::class, 'login']);

// Public auth routes (no authentication required)
Route::post('register', [RegisterController::class, 'register']);
Route::post('verify', [VerifyController::class, 'verify']);
Route::post('resend-otp', [VerifyController::class, 'resendOtp']);

Route::prefix('forgot-password')->group(function () {
    Route::post('send', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('reset', [ForgotPasswordController::class, 'resetPassword']);
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
});
