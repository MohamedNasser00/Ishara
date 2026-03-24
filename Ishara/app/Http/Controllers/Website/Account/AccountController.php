<?php

namespace App\Http\Controllers\Website\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Account\UpdateNameRequest;
use App\Services\Website\Account\AccountService;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Get user profile.
     */
    public function profile(): JsonResponse
    {
        return $this->accountService->getProfile();
    }

    /**
     * Update user name.
     */
    public function updateName(UpdateNameRequest $request): JsonResponse
    {
        return $this->accountService->updateName($request->validated());
    }

    /**
     * Logout user.
     */
    public function logout(): JsonResponse
    {
        return $this->accountService->logout();
    }

    /**
     * Clear user progress.
     */
    public function clearProgress(): JsonResponse
    {
        return $this->accountService->clearProgress();
    }
}
