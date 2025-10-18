<?php

namespace App\Services\Website\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\HandleResponse;
use App\Http\Resources\Website\Auth\UserResource;

class AccountService
{
    public function getUserProfile()
    {
        $user = auth()->user();
        
        if (!$user) {
            return HandleResponse::fail('User not authenticated.', [], 401);
        }

        return HandleResponse::success('User profile retrieved successfully.', [
            'user' => new UserResource($user)
        ]);
    }

    public function updateUserProfile(array $data)
    {
        $user = auth()->user();
        
        if (!$user) {
            return HandleResponse::fail('User not authenticated.', [], 401);
        }

        // Update only the fields that are provided
        $user->fill($data);
        $user->save();

        return HandleResponse::success('Profile updated successfully.', [
            'user' => new UserResource($user->fresh())
        ]);
    }

    public function updatePassword(array $data)
    {
        $user = auth()->user();
        
        if (!$user) {
            return HandleResponse::fail('User not authenticated.', [], 401);
        }

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            return HandleResponse::fail('Current password is incorrect.', [], 422);
        }

        // Update password
        $user->password = Hash::make($data['password']);
        $user->save();

        return HandleResponse::success('Password updated successfully.');
    }

    public function logout()
    {
        $user = auth()->user();
        
        if (!$user) {
            return HandleResponse::fail('User not authenticated.', [], 401);
        }

        // Get user data before revoking tokens
        $userData = new UserResource($user);

        // Revoke all tokens for the current user
        $user->tokens()->delete();

        return HandleResponse::success('Logged out successfully.', [
            'user' => $userData
        ]);
    }
} 