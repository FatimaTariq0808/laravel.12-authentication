<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; padding: 30px; }
        .email-container { background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; }
        .header { background: #007bff; color: white; padding: 10px; border-radius: 10px 10px 0 0; }
        .content { margin-top: 20px; font-size: 16px; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>

<div class="email-container">
    <div class="header">
        <h2>Welcome to Laravel 🎉</h2>
    </div>

    <div class="content">
        <h1>Welcome {{ $user->name }}!</h1>
        <p>Thanks for Logging In.</p>
        <p>Enjoy the experience! 🚀</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Our App. All rights reserved.
    </div>
</div>

</body>
</html>
