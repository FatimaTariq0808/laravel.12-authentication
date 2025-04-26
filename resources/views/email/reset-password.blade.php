<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .email-container { background: #ffffff; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; }
        .header { background: #dc3545; color: white; padding: 15px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { margin-top: 20px; font-size: 16px; color: #333; }
        .button { display: inline-block; background: #dc3545; color: white; padding: 10px 20px; margin-top: 20px; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>

<div class="email-container">
    <div class="header">
        <h2>Password Reset Request</h2>
    </div>

    <div class="content">
        <p>Hello,</p>
        <p>We received a request to reset your password.</p>

        <p><strong>Click the button below to reset your password:</strong></p>

        <a href="{{ $resetURL }}" class="button">Reset Password</a>

        <p>If you did not request a password reset, you can ignore this email.</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Our App. All rights reserved.
    </div>
</div>

</body>
</html>
