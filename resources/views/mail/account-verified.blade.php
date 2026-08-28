<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>{{ __('Hi :name,', ['name' => $userName]) }}</p>
    <p>{{ __('Your identity verification has been approved. Your PickDrop account is now verified.') }}</p>
    @if($isSelf)
        <p>{{ __('Next, add your pickup location, drop / office location, and office timing in the app.') }}</p>
    @else
        <p>{{ __('Next, add your children details in the app so we can arrange their pick and drop.') }}</p>
    @endif
    <p>{{ __('If you did not create this account, please contact support.') }}</p>
</body>
</html>
