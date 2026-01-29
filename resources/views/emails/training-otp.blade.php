<!DOCTYPE html>
<html>
<head>
    <title>Training Authorization</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 10px;">
        <h2 style="color: #d97706;">Start Training Immediately</h2>
        <p>You have requested to start the training <strong>{{ $trainingTitle }}</strong> immediately.</p>
        <p>Use the following One-Time Password (OTP) to authorize this action:</p>
        
        <div style="background: #fff; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0; border: 1px solid #ddd;">
            <span style="font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333;">{{ $otp }}</span>
        </div>
        
        <p>This code is valid for 10 minutes.</p>
        <p>If you did not request this, please ignore this email.</p>
    </div>
</body>
</html>