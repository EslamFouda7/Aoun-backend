<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
</head>
<body>

    <p>Hello,</p>

    <p>You requested a password reset for your account.</p>

    <p>
        Click the link below to reset your password:
    </p>

    <p>
        <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <p>
        If you did not request this, please ignore this email. Your password will remain unchanged.
    </p>

    <p>Best regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>
