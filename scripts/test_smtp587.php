<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'scheme=' . var_export(config('mail.mailers.smtp.scheme'), true) . PHP_EOL;
echo 'host=' . config('mail.mailers.smtp.host') . PHP_EOL;
echo 'port=' . config('mail.mailers.smtp.port') . PHP_EOL;
echo 'ehlo=' . config('mail.mailers.smtp.local_domain') . PHP_EOL;
echo 'from=' . config('mail.from.address') . ' / ' . config('mail.from.name') . PHP_EOL;

try {
    Illuminate\Support\Facades\Mail::mailer('smtp')->raw(
        'PickDrop SMTP connectivity test at ' . now()->toDateTimeString(),
        function ($message) {
            $message->to((string) config('mail.from.address'))
                ->subject('PickDrop SMTP test');
        }
    );
    echo "SEND_OK\n";
} catch (Throwable $e) {
    echo 'SEND_FAIL: ' . $e->getMessage() . PHP_EOL;
}
