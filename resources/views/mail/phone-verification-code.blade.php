<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>{{ __('Hi :name,', ['name' => $userName]) }}</p>
    <p>{{ __('Your phone verification code for :phone is:', ['phone' => $phone]) }} <strong style="font-size: 18px; letter-spacing: 2px;">{{ $code }}</strong></p>
    <p>{{ __('This code expires in 10 minutes. If you did not request this, you can ignore this email.') }}</p>
</body>
</html>
