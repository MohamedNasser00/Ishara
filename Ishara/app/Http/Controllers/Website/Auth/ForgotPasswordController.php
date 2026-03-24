<?php

namespace App\Http\Controllers\Website\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Auth\ForgotPasswordRequest;
use App\Http\Requests\Website\Auth\VerifyForgotOtpRequest;
use App\Http\Requests\Website\Auth\ResetPasswordRequest;
use App\Services\Website\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        return app(AuthService::class)->sendForgotOtp($request->validated());
    }

    public function verifyOtp(VerifyForgotOtpRequest $request): JsonResponse
    {
        return app(AuthService::class)->verifyForgotOtp($request->validated());
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return app(AuthService::class)->resetPassword($request->validated());
    }

    public function resendOtp(\App\Http\Requests\Website\Auth\ResendForgotOtpRequest $request): JsonResponse
    {
        return app(AuthService::class)->resendForgotOtp($request->validated());
    }
} 