<?php

namespace App\Http\Controllers\Website\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Auth\VerifyOtpRequest;
use App\Http\Requests\Website\Auth\ResendOtpRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Services\HandleResponse;
use App\Services\Website\Auth\AuthService;

class VerifyController extends Controller
{
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        return app(AuthService::class)->verifyOtp($request->validated());
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        return app(AuthService::class)->resendOtp($request->validated());
    }
} 