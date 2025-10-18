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

            $phoneExists = User::where('phone', $data['phone'])
                ->whereNotNull('email_verified_at')
                ->whereNull('deleted_at')
                ->exists();

            Log::info('Email exists check result: ' . ($emailExists ? 'true' : 'false'));
            Log::info('Phone exists check result: ' . ($phoneExists ? 'true' : 'false'));

            if ($emailExists) {
                Log::info('Email already exists and verified - returning error');
                return HandleResponse::fail('Email already exists', ['email' => ['This email is already registered']], 422);
            }

            if ($phoneExists) {
                Log::info('Phone already exists and verified - returning error');
                return HandleResponse::fail('Phone already exists', ['phone' => ['This phone number is already registered']], 422);
            }

            // 2. Check for any existing user (verified or not) with same email or phone
            Log::info('Checking for any existing user with same email or phone...');
            $existingUser = User::where(function($query) use ($data) {
                    $query->where('email', $data['email'])
                          ->orWhere('phone', $data['phone']);
                })
                ->whereNull('deleted_at')
                ->first();

            if ($existingUser) {
                Log::info('Found existing user - updating and sending OTP');
                
                // Update user data
                $existingUser->first_name = $data['first_name'];
                $existingUser->last_name = $data['last_name'];
                $existingUser->gender = $data['gender'];
                $existingUser->phone = $data['phone'];
                $existingUser->parent_name = $data['parent_name'];
                $existingUser->parent_number = $data['parent_number'];
                $existingUser->password = Hash::make($data['password']);
                $existingUser->otp_code = $otp;
                $existingUser->otp_expires_at = now()->addMinutes(10);
                $existingUser->is_verified = false;
                $existingUser->save();
                Log::info('Existing user updated');

                // Send OTP email
                Log::info('Attempting to send OTP email to existing user...');
                try {
                    Log::info('Creating OTPMail instance...');
                    $mailInstance = new OTPMail($otp);
                    Log::info('OTPMail instance created successfully');
                    
                    Log::info('Sending email to: ' . $existingUser->email);
                    Mail::to($existingUser->email)->send($mailInstance);
                    Log::info('✅ OTP email sent successfully to: ' . $existingUser->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send OTP email to: ' . $existingUser->email . ' Error: ' . $e->getMessage());
                    Log::error('Exception details:', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                Log::info('=== REGISTRATION PROCESS COMPLETED (EXISTING USER) ===');
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
                ->where(function($query) use ($data) {
                    $query->where('email', $data['email'])
                          ->orWhere('phone', $data['phone']);
                })
                ->whereNotNull('deleted_at')
                ->first();

            if ($softDeletedUser) {
                Log::info('Found soft deleted user - restoring account');
                $softDeletedUser->restore();
                $softDeletedUser->first_name = $data['first_name'];
                $softDeletedUser->last_name = $data['last_name'];
                $softDeletedUser->gender = $data['gender'];
                $softDeletedUser->phone = $data['phone'];
                $softDeletedUser->parent_name = $data['parent_name'];
                $softDeletedUser->parent_number = $data['parent_number'];
                $softDeletedUser->password = Hash::make($data['password']);
                $softDeletedUser->otp_code = $otp;
                $softDeletedUser->otp_expires_at = now()->addMinutes(10);
                $softDeletedUser->email_verified_at = null;
                $softDeletedUser->is_verified = false;
                $softDeletedUser->save();
                Log::info('Soft deleted user restored and updated');

                // Send OTP email
                Log::info('Attempting to send OTP email to soft deleted user...');
                try {
                    Log::info('Creating OTPMail instance...');
                    $mailInstance = new OTPMail($otp);
                    Log::info('OTPMail instance created successfully');
                    
                    Log::info('Sending email to: ' . $softDeletedUser->email);
                    Mail::to($softDeletedUser->email)->send($mailInstance);
                    Log::info('✅ OTP email sent successfully to: ' . $softDeletedUser->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send OTP email to: ' . $softDeletedUser->email . ' Error: ' . $e->getMessage());
                    Log::error('Exception details:', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                Log::info('=== REGISTRATION PROCESS COMPLETED (SOFT DELETED USER) ===');
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
                'phone' => $data['phone'],
                'email' => $data['email'],
                'parent_name' => $data['parent_name'],
                'parent_number' => $data['parent_number'],
                'password' => Hash::make($data['password']),
                'is_verified' => false,
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);
            Log::info('New user created with ID: ' . $user->id);

            // Send OTP email
            Log::info('Attempting to send OTP email to new user...');
            try {
                Log::info('Creating OTPMail instance...');
                $mailInstance = new OTPMail($otp);
                Log::info('OTPMail instance created successfully');
                
                Log::info('Sending email to: ' . $user->email);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
            } catch (Exception $e) {
                Log::error('❌ Failed to send OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                Log::error('Exception details:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            Log::info('=== REGISTRATION PROCESS COMPLETED (NEW USER) ===');
            return HandleResponse::success('User registered successfully. Please verify your email with the OTP sent.', [
                'email' => $user->email,
                'requiresOTP' => true
            ], 201);

        } catch (Exception $e) {
            Log::error('❌ Register Exception: ' . $e->getMessage(), ['exception' => $e]);
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

            // Check if this OTP was sent for password reset (by checking if user is already verified)
            if (!is_null($user->email_verified_at)) {
                return HandleResponse::fail('This OTP is for password reset. Please use the correct verification process.', [], 422);
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

            Log::info('User found with ID: ' . $user->id);

            if (!Hash::check($data['password'], $user->password)) {
                Log::info('Password incorrect for user: ' . $user->email);
                return HandleResponse::fail('Invalid credentials', ['password' => ['Incorrect password']], 401);
            }

            Log::info('Password verified successfully');

            if (is_null($user->email_verified_at)) {
                Log::info('User email not verified - sending OTP');
                $otp = rand(1000, 9999);
                $user->otp_code = $otp;
                $user->otp_expires_at = now()->addMinutes(10);
                $user->save();
                Log::info('OTP generated and saved: ' . $otp);
                
                try {
                    Log::info('Creating OTPMail instance for login...');
                    $mailInstance = new OTPMail($otp);
                    Log::info('OTPMail instance created successfully');
                    
                    Log::info('Sending login OTP email to: ' . $user->email);
                    Mail::to($user->email)->send($mailInstance);
                    Log::info('✅ Login OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
                } catch (Exception $e) {
                    Log::error('❌ Failed to send login OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                    Log::error('Exception details:', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                Log::info('=== LOGIN PROCESS COMPLETED (UNVERIFIED USER) ===');
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. OTP resent. Please verify your email with the OTP sent.',
                    'requiresOTP' => true,
                    'errors' => [
                        'email' => $user->email,
                    ]
                ], 403);
            }

            Log::info('User email verified - creating token');
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Token created successfully');

            Log::info('=== LOGIN PROCESS COMPLETED (SUCCESS) ===');
            return HandleResponse::success('Login successful', [
                'token' => $token,
                'user' => $user
            ]);

        } catch (Exception $e) {
            Log::error('❌ Login Exception: ' . $e->getMessage(), ['exception' => $e]);
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return HandleResponse::fail('Login failed', ['error' => ['Something went wrong']], 500);
        }
    }

    public function sendForgotOtp(array $data)
    {
        try {
            Log::info('=== STARTING SEND FORGOT OTP PROCESS ===');
            Log::info('Send forgot OTP data received:', ['email' => $data['email']]);
            
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                Log::info('User not found with email: ' . $data['email']);
                return HandleResponse::fail('User not found', [], 404);
            }

            // Check if user email is verified
            if (is_null($user->email_verified_at)) {
                Log::info('User email not verified - cannot reset password');
                return HandleResponse::fail('Email not verified. Please verify your email first.', [], 422);
            }

            Log::info('User found with ID: ' . $user->id . ' and email verified');
            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            Log::info('Reset OTP generated and saved: ' . $otp);

            try {
                Log::info('Creating ResetPasswordOtpMail instance...');
                $mailInstance = new ResetPasswordOtpMail($otp);
                Log::info('ResetPasswordOtpMail instance created successfully');
                
                Log::info('Sending reset password OTP email to: ' . $user->email);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ Reset password OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
            } catch (Exception $e) {
                Log::error('❌ Failed to send reset password OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                Log::error('Exception details:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            Log::info('=== SEND FORGOT OTP PROCESS COMPLETED ===');
            return HandleResponse::success('OTP sent to your email.');

        } catch (Exception $e) {
            Log::error('❌ Send Forgot OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

            // Check if user email is verified
            if (is_null($user->email_verified_at)) {
                return HandleResponse::fail('Email not verified. Please verify your email first.', [], 422);
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

            // Check if user email is verified
            if (is_null($user->email_verified_at)) {
                return HandleResponse::fail('Email not verified. Please verify your email first.', [], 422);
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
            Log::info('=== STARTING RESEND OTP PROCESS ===');
            Log::info('Email: ' . $data['email']);

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                Log::info('User not found with email: ' . $data['email']);
                return HandleResponse::fail('User not found', [], 404);
            }

            // Check if user is already verified
            if (!is_null($user->email_verified_at)) {
                Log::info('User already verified: ' . $data['email']);
                return HandleResponse::fail('Email is already verified', [], 422);
            }

            // Generate new OTP
            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            Log::info('New OTP generated and saved: ' . $otp);

            // Send OTP email
            try {
                Log::info('Creating OTPMail instance for resend...');
                $mailInstance = new OTPMail($otp);
                Log::info('OTPMail instance created successfully');
                
                Log::info('Sending resend OTP email to: ' . $user->email);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ Resend OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
            } catch (Exception $e) {
                Log::error('❌ Failed to send resend OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                Log::error('Exception details:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return HandleResponse::fail('Failed to send OTP email', ['error' => ['Unable to send OTP. Please try again later.']], 500);
            }

            Log::info('=== RESEND OTP PROCESS COMPLETED ===');
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
            Log::info('=== CHECKING MAIL CONFIGURATION ===');
            
            // Log current mail configuration
            $mailConfig = config('mail');
            Log::info('Mail configuration:', [
                'driver' => $mailConfig['default'],
                'from_address' => $mailConfig['from']['address'],
                'from_name' => $mailConfig['from']['name']
            ]);
            
            // Check if mail driver is configured
            if (empty($mailConfig['default'])) {
                throw new Exception('Mail driver not configured');
            }
            
            // Check if from address is configured
            if (empty($mailConfig['from']['address'])) {
                throw new Exception('Mail from address not configured');
            }
            
            Log::info('Mail configuration validation passed');
            
            // Try to send a test email
            Log::info('Attempting to send test email...');
            Mail::raw('Test email for mail configuration', function ($message) {
                $message->to(config('mail.from.address'))
                       ->subject('Mail Configuration Test');
            });
            
            Log::info('✅ Mail configuration check successful. Email sent to: ' . config('mail.from.address'));
            
        } catch (Exception $e) {
            Log::error('❌ Mail configuration check failed. Error: ' . $e->getMessage());
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw exception, just log the error
            Log::warning('Mail configuration issue detected, but continuing with registration process');
        }
    }

    public function resendForgotOtp(array $data)
    {
        try {
            Log::info('=== STARTING RESEND FORGOT PASSWORD OTP PROCESS ===');
            Log::info('Email: ' . $data['email']);

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                Log::info('User not found with email: ' . $data['email']);
                return HandleResponse::fail('User not found', [], 404);
            }

            // Check if user email is verified (required for forgot password)
            if (is_null($user->email_verified_at)) {
                Log::info('User email not verified - cannot resend forgot password OTP');
                return HandleResponse::fail('Email not verified. Please verify your email first.', [], 422);
            }

            // Generate new OTP
            $otp = rand(1000, 9999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            Log::info('New forgot password OTP generated and saved: ' . $otp);

            // Send forgot password OTP email
            try {
                Log::info('Creating ResetPasswordOtpMail instance for resend...');
                $mailInstance = new ResetPasswordOtpMail($otp);
                Log::info('ResetPasswordOtpMail instance created successfully');
                
                Log::info('Sending resend forgot password OTP email to: ' . $user->email);
                Mail::to($user->email)->send($mailInstance);
                Log::info('✅ Resend forgot password OTP email sent successfully to: ' . $user->email . ' with OTP: ' . $otp);
            } catch (Exception $e) {
                Log::error('❌ Failed to send resend forgot password OTP email to: ' . $user->email . ' Error: ' . $e->getMessage());
                Log::error('Exception details:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return HandleResponse::fail('Failed to send OTP email', ['error' => ['Unable to send OTP. Please try again later.']], 500);
            }

            Log::info('=== RESEND FORGOT PASSWORD OTP PROCESS COMPLETED ===');
            return HandleResponse::success('Forgot password OTP resent successfully. Please check your email.', [
                'email' => $user->email,
                'expires_at' => $user->otp_expires_at
            ]);

        } catch (Exception $e) {
            Log::error('❌ Resend Forgot Password OTP Exception: ' . $e->getMessage(), ['exception' => $e]);
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return HandleResponse::fail('Failed to resend forgot password OTP', ['error' => ['Something went wrong']], 500);
        }
    }
} 