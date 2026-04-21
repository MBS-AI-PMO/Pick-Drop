<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>{{ __('Hi :name,', ['name' => $userName]) }}</p>
    <p>{{ __('Your email verification code is:') }} <strong style="font-size: 18px; letter-spacing: 2px;">{{ $code }}</strong></p>
    <p>{{ __('This code expires in 30 minutes. If you did not request this, you can ignore this email.') }}</p>
</body>
</html>
