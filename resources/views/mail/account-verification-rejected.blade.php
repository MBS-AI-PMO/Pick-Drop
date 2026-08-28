<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>{{ __('Hi :name,', ['name' => $userName]) }}</p>
    <p>{{ __('Your identity verification could not be approved.') }}</p>
    <p><strong>{{ __('Reason:') }}</strong> {{ $reason }}</p>
    <p>{{ __('Please update your documents and submit verification again from the app.') }}</p>
</body>
</html>
