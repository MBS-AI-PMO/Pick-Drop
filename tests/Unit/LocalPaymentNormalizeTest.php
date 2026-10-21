<?php

use App\Services\InvoiceService;
use App\Services\LocalPaymentService;

it('normalizes pakistani mobile numbers', function () {
    $svc = new LocalPaymentService(Mockery::mock(InvoiceService::class));

    expect($svc->normalizePkMobile('03001234567'))->toBe('03001234567')
        ->and($svc->normalizePkMobile('3001234567'))->toBe('03001234567')
        ->and($svc->normalizePkMobile('+92 300 1234567'))->toBe('03001234567');
});

it('rejects an invalid mobile number', function () {
    $svc = new LocalPaymentService(Mockery::mock(InvoiceService::class));
    $svc->normalizePkMobile('12345');
})->throws(RuntimeException::class);

it('takes the last six cnic digits', function () {
    $svc = new LocalPaymentService(Mockery::mock(InvoiceService::class));

    expect($svc->cnicLastSix('35202-1234567-1'))->toBe('345671')
        ->and($svc->cnicLastSix('123456'))->toBe('123456');
});
