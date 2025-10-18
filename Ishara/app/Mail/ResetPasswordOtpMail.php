<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        Log::info('ResetPasswordOtpMail constructor called with OTP: ' . $otp);
        $this->otp = $otp;
        Log::info('ResetPasswordOtpMail instance created successfully');
    }

    public function build()
    {
        Log::info('ResetPasswordOtpMail build() method called');
        Log::info('Building reset password email with OTP: ' . $this->otp);
        
        try {
            $mail = $this->subject('Reset Your Password - OTP Code')
                        ->view('emails.reset-password-otp')
                        ->with([
                            'otp' => $this->otp,
                        ]);
            
            Log::info('ResetPasswordOtpMail build() completed successfully');
            return $mail;
        } catch (\Exception $e) {
            Log::error('ResetPasswordOtpMail build() failed: ' . $e->getMessage());
            Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 