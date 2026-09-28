<?php

use App\Support\EasyPaisaSigner;
use App\Support\JazzCashSigner;

it('creates a stable jazzcash hash and rejects a fake one', function () {
    $fields = [
        'pp_Amount' => '10000',
        'pp_MerchantID' => 'MC123',
        'pp_TxnRefNo' => 'JC123',
    ];
    $salt = 'integrity-salt';
    $hash = JazzCashSigner::hash($fields, $salt);

    expect($hash)->toHaveLength(64)
        ->and(JazzCashSigner::matches($fields, $salt, $hash))->toBeTrue()
        ->and(JazzCashSigner::matches($fields, $salt, 'DEADBEEF'))->toBeFalse()
        ->and(JazzCashSigner::matches($fields, '', $hash))->toBeFalse()
        ->and(JazzCashSigner::matches($fields, $salt, null))->toBeFalse();
});

it('creates a stable easypaisa hash and rejects a fake one', function () {
    $payload = [
        'orderId' => 'EP123',
        'storeId' => 'STORE1',
        'transactionAmount' => '250.00',
    ];
    $key = 'hash-key';
    $hash = EasyPaisaSigner::hash($payload, $key);

    expect(EasyPaisaSigner::matches($payload, $key, $hash))->toBeTrue()
        ->and(EasyPaisaSigner::matches($payload, $key, 'fake-hash'))->toBeFalse()
        ->and(EasyPaisaSigner::matches($payload, '', $hash))->toBeFalse();
});
