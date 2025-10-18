<?php

namespace App\Http\Controllers\Website\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Auth\UpdateAccountRequest;
use App\Http\Requests\Website\Auth\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use App\Services\Website\Auth\AccountService;

class AccountController extends Controller
{
    public function show(): JsonResponse
    {
        return app(AccountService::class)->getUserProfile();
    }

    public function update(UpdateAccountRequest $request): JsonResponse
    {
        return app(AccountService::class)->updateUserProfile($request->validated());
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        return app(AccountService::class)->updatePassword($request->validated());
    }

    public function logout(): JsonResponse
    {
        return app(AccountService::class)->logout();
    }
} 