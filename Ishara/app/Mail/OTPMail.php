<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        Log::info('OTPMail constructor called with OTP: ' . $otp);
        $this->otp = $otp;
        Log::info('OTPMail instance created successfully');
    }

    public function build()
    {
        Log::info('OTPMail build() method called');
        Log::info('Building email with OTP: ' . $this->otp);
        
        try {
            $mail = $this->subject('Your OTP Code for Verification')
                        ->view('emails.otp')
                        ->with([
                            'otp' => $this->otp,
                        ]);
            
            Log::info('OTPMail build() completed successfully');
            return $mail;
        } catch (\Exception $e) {
            Log::error('OTPMail build() failed: ' . $e->getMessage());
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
