<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: white !important; padding: 20px; text-align: center;">
    <div style="max-width: 500px; margin: auto; background: white !important; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #333;">OTP Verification</h2>
        <p style="font-size: 16px; color: #555;">Use the following OTP code to verify your email:</p>
        <h1 style="background: #fb443b; color: white; padding: 15px; border-radius: 5px; display: inline-block;">{{ $otp }}</h1>
        <p style="font-size: 14px; color: #888;">This OTP is valid for 10 minutes.</p>
        <p style="font-size: 14px; color: #888;">If you didn't request this, please ignore this email.</p>
        <p style="font-size: 14px; color: #888;">Regards, Ishara.</p>
    </div>
</body>
</html>
