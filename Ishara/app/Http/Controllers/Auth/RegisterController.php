<?php

namespace App\Http\Controllers\Website\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Services\HandleResponse;
use App\Services\Website\Auth\AuthService;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        return app(AuthService::class)->registerUser($request->validated());
    }
} 