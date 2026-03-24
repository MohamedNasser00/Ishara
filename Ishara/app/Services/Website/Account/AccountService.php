<?php

namespace App\Services\Website\Account;

use App\Http\Resources\UserResource;
use App\Services\HandleResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class AccountService
{
    /**
     * Get authenticated user profile data.
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            return HandleResponse::success('Profile data retrieved successfully', [
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('❌ Get Profile Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to retrieve profile', [], 500);
        }
    }

    /**
     * Update user name (first and last).
     */
    public function updateName(array $data)
    {
        try {
            $user = Auth::user();
            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]);

            return HandleResponse::success('Name updated successfully', [
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('❌ Update Name Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to update name', [], 500);
        }
    }

    /**
     * Logout user and revoke tokens.
     */
    public function logout()
    {
        try {
            $user = Auth::user();
            
            // Revoke the token that was used to authenticate the current request...
            $user->currentAccessToken()->delete();

            return HandleResponse::success('Logged out successfully');
        } catch (Exception $e) {
            Log::error('❌ Logout Exception: ' . $e->getMessage());
            return HandleResponse::fail('Logout failed', [], 500);
        }
    }

    /**
     * Clear all user progress (Learn, Practice, Test).
     */
    public function clearProgress()
    {
        try {
            $user = Auth::user();
            
            // Delete progress from all three modules
            $user->userLessons()->delete();
            $user->userPracticeProgress()->delete();
            $user->userTestProgress()->delete();

            return HandleResponse::success('Account progress cleared successfully');
        } catch (Exception $e) {
            Log::error('❌ Clear Progress Exception: ' . $e->getMessage());
            return HandleResponse::fail('Failed to clear progress', [], 500);
        }
    }
}
