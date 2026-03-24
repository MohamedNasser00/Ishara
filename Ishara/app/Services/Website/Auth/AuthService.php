<?php

namespace App\Services\Website\Auth;

use App\Mail\OTPMail;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\HandleResponse;
use Exception;

class AuthService
{
    public function registerUser(array $data)
    {
        try {
            Log::info('=== STARTING REGISTRATION PROCESS ===');
            Log::info('Registration data received:', $data);
            
            // Check mail configuration
            $this->checkMailConfiguration();
            
            $otp = rand(1000, 9999);
            Log::info('Generated OTP: ' . $otp);

            // 1. Check for existing verified user (not soft deleted)
            Log::info('Checking for existing verified user...');
            $emailExists = User::where('email', $data['email'])
                ->whereNotNull('email_verified_at')
                ->whereNull('deleted_at')
                ->exists();

            Log::info('Email exists check result: ' . ($emailExists ? 'true' : 'false'));

            if ($emailExists) {
                Log::info('Email already exists and verified - returning error');
                return HandleResponse::fail('Email already exists', ['email' => ['This email is already registered']], 422);
            }

            // 2. Check for any existing user (verified or not) with same email
            Log::info('Checking for any existing user with same email...');
            $existingUser = User::where('email', $data['email'])
                ->whereNull('deleted_at')
                ->first();

            if ($existingUser) {
                Log::info('Found existing user - updating and sending OTP');
                
                // Update user data
                $existingUser->first_name = $data['first_name'];
                $existingUser->last_name = $data['last_name'];
                $existingUser->gender = $data['gender'];
                $existingUser->date_of_birth = $data['date_of_birth'] ?? $existingUser->date_of_birth;
                $existingUser->password = Hash::make($data['password']);
                $existingUser->otp_code = $otp;
                $existingUser->otp_expires_at = now()->addMinutes(10);
                $existingUser->is_verified = false;
                $existingUser->save();
                Log::info('Existing user updated');

                // Send OTP email
                Log::info('Attempting to send OTP email to existing user...');
                try {
                    $mailInstance = new OTPMail($otp);
                    Mail::to($existingUser->email)->send($mailInstance);
                    Log::info('✅ OTP email sent successfully to: ' . $existingUser->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send OTP email to: ' . $existingUser->email . ' Error: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. OTP resent. Please verify your email with the OTP sent.',
                    'requiresOTP' => true,
                    'errors' => [
                        'email' => $existingUser->email
                    ]
                ], 403);
            }

            // 3. Check for soft deleted user
            Log::info('Checking for soft deleted user...');
            $softDeletedUser = User::withTrashed()
                ->where('email', $data['email'])
                ->whereNotNull('deleted_at')
                ->first();

            if ($softDeletedUser) {
                Log::info('Found soft deleted user - restoring account');
                $softDeletedUser->restore();
                $softDeletedUser->first_name = $data['first_name'];
                $softDeletedUser->last_name = $data['last_name'];
                $softDeletedUser->gender = $data['gender'];
                $softDeletedUser->date_of_birth = $data['date_of_birth'];
                $softDeletedUser->password = Hash::make($data['password']);
                $softDeletedUser->otp_code = $otp;
                $softDeletedUser->otp_expires_at = now()->addMinutes(10);
                $softDeletedUser->email_verified_at = null;
                $softDeletedUser->is_verified = false;
                $softDeletedUser->save();
                Log::info('Soft deleted user restored and updated');

                // Send OTP email
                try {
                    $mailInstance = new OTPMail($otp);
                    Mail::to($softDeletedUser->email)->send($mailInstance);
                    Log::info('✅ OTP email sent successfully to: ' . $softDeletedUser->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send OTP email to: ' . $softDeletedUser->email . ' Error: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. OTP resent. Please verify your email with the OTP sent.',
                    'requiresOTP' => true,
                    'errors' => [
                        'email' => $softDeletedUser->email
                    ]
                ], 403);
            }

            // 4. Create new user
            Log::info('Creating new user...');
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'email' => $data['email'],
                'date_of_birth' => $data['date_of_birth'],
                'password' => Hash::make($data['password']),
                'is_verified' => false,
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);
            Log::info('New user created with ID: ' . $user->id);

            // Send OTP email
            try {
                $mailInstance = new OTPMail($otp);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
            } catch (Exception $e) {
                Log::error('❌ Failed to send OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
            }

            Log::info('=== REGISTRATION PROCESS COMPLETED ===');
            return HandleResponse::success('User registered successfully. Please verify your email with the OTP sent.', [
                'email' => $user->email,
                'requiresOTP' => true
            ], 201);

        } catch (Exception $e) {
            Log::error('❌ Register Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Registration failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function verifyOtp(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            if ($user->otp_code !== $data['otp_code']) {
                return HandleResponse::fail('Invalid OTP', [], 422);
            }

            if (Carbon::now()->gt($user->otp_expires_at)) {
                return HandleResponse::fail('OTP expired', [], 422);
            }

            $user->email_verified_at = now();
            $user->is_verified = true;
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return HandleResponse::success('Email verified successfully', [
                'user' => $user,
                'token' => $token
            ]);

        } catch (Exception $e) {
            Log::error('Verify OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('OTP verification failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function loginUser(array $data)
    {
        try {
            Log::info('=== STARTING LOGIN PROCESS ===');
            Log::info('Login data received:', ['email' => $data['email']]);
            
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                Log::info('User not found with email: ' . $data['email']);
                return HandleResponse::fail('Invalid credentials', ['email' => ['No account found with this email']], 401);
            }

            if (!Hash::check($data['password'], $user->password)) {
                Log::info('Password incorrect for user: ' . $user->email);
                return HandleResponse::fail('Invalid credentials', ['password' => ['Incorrect password']], 401);
            }

            if (is_null($user->email_verified_at)) {
                Log::info('User email not verified - sending OTP');
                $otp = rand(1000, 9999);
                $user->otp_code = $otp;
                $user->otp_expires_at = now()->addMinutes(10);
                $user->save();
                
                try {
                    $mailInstance = new OTPMail($otp);
                    Mail::to($user->email)->send($mailInstance);
                    Log::info('✅ Login OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send login OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. OTP resent. Please verify your email with the OTP sent.',
                    'requiresOTP' => true,
                    'errors' => [
                        'email' => $user->email,
                    ]
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return HandleResponse::success('Login successful', [
                'token' => $token,
                'user' => $user
            ]);

        } catch (Exception $e) {
            Log::error('❌ Login Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Login failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function sendForgotOtp(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            if (is_null($user->email_verified_at)) {
                return HandleResponse::fail('Email not verified. Please verify your email first.', [], 422);
            }

            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                $mailInstance = new ResetPasswordOtpMail($otp);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ Reset password OTP email sent successfully');
            } catch (Exception $e) {
                Log::error('❌ Failed to send reset password OTP email');
            }

            return HandleResponse::success('OTP sent to your email.');

        } catch (Exception $e) {
            Log::error('❌ Send Forgot OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Failed to send OTP', ['error' => ['Something went wrong']], 500);
        }
    }

    public function verifyForgotOtp(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            if ($user->otp_code !== $data['otp_code']) {
                return HandleResponse::fail('Invalid OTP', [], 422);
            }
            if (now()->gt($user->otp_expires_at)) {
                return HandleResponse::fail('OTP expired', [], 422);
            }
            return HandleResponse::success('OTP verified. You can now reset your password.');

        } catch (Exception $e) {
            Log::error('Verify Forgot OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('OTP verification failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function resetPassword(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            if ($user->otp_code !== $data['otp_code']) {
                return HandleResponse::fail('Invalid OTP', [], 422);
            }
            if (now()->gt($user->otp_expires_at)) {
                return HandleResponse::fail('OTP expired', [], 422);
            }
            $user->password = Hash::make($data['password']);
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            return HandleResponse::success('Password reset successfully.');

        } catch (Exception $e) {
            Log::error('Reset Password Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Password reset failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function resendOtp(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            if (!is_null($user->email_verified_at)) {
                return HandleResponse::fail('Email is already verified', [], 422);
            }

            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                $mailInstance = new OTPMail($otp);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ Resend OTP email sent successfully');
            } catch (Exception $e) {
                Log::error('❌ Failed to send resend OTP email');
                return HandleResponse::fail('Failed to send OTP email', ['error' => ['Unable to send OTP. Please try again later.']], 500);
            }

            return HandleResponse::success('OTP resent successfully. Please check your email.', [
                'email' => $user->email,
                'expires_at' => $user->otp_expires_at
            ]);

        } catch (Exception $e) {
            Log::error('Resend OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Failed to resend OTP', ['error' => ['Something went wrong']], 500);
        }
    }

    private function checkMailConfiguration()
    {
        try {
            $mailConfig = config('mail');
            if (empty($mailConfig['default']) || empty($mailConfig['from']['address'])) {
                throw new Exception('Mail configuration missing');
            }
        } catch (Exception $e) {
            Log::warning('Mail configuration issue detected');
        }
    }

    public function resendForgotOtp(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                return HandleResponse::fail('User not found', [], 404);
            }

            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                $mailInstance = new ResetPasswordOtpMail($otp);
                Mail::to($user->email)->send($mailInstance);
            } catch (Exception $e) {
                Log::error('❌ Failed to send resend forgot password OTP email');
                return HandleResponse::fail('Failed to send OTP email', [], 500);
            }

            return HandleResponse::success('Forgot password OTP resent successfully.', [
                'email' => $user->email,
                'expires_at' => $user->otp_expires_at
            ]);

        } catch (Exception $e) {
            Log::error('❌ Resend Forgot Password OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            return HandleResponse::fail('Failed to resend forgot password OTP', [], 500);
        }
    }
}