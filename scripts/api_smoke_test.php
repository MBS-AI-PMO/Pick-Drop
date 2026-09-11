<?php

/**
 * Pick-Drop mobile API smoke test.
 * Run: php scripts/api_smoke_test.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ParentSelfVerification;
use App\Models\DriverVerification;
use App\Models\DriverVehicleVerification;

$base = getenv('API_BASE') ?: 'http://127.0.0.1/Pick-Drop/public/api';
$stamp = date('YmdHis');
$results = [];
$pass = 0;
$fail = 0;

function out(string $msg): void
{
    echo $msg . PHP_EOL;
}

function record(array &$results, int &$pass, int &$fail, string $name, int $status, $body, bool $ok, string $note = ''): void
{
    $icon = $ok ? 'PASS' : 'FAIL';
    if ($ok) {
        $pass++;
    } else {
        $fail++;
    }
    $snippet = is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : (string) $body;
    if (strlen($snippet) > 280) {
        $snippet = substr($snippet, 0, 280) . '...';
    }
    $results[] = compact('name', 'status', 'ok', 'note') + ['body' => $snippet];
    out(sprintf('[%s] %-45s HTTP %s %s', $icon, $name, $status, $note));
    if (!$ok) {
        out('      -> ' . $snippet);
    }
}

function api(string $method, string $url, ?array $json = null, ?string $token = null, array $multipart = []): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    if ($multipart) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
    } elseif ($json !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno) {
        return ['status' => 0, 'body' => ['error' => $err], 'raw' => $err];
    }

    $decoded = json_decode((string) $raw, true);
    return ['status' => $status, 'body' => $decoded ?? $raw, 'raw' => $raw];
}

function tinyPng(string $path): string
{
    // 1x1 PNG
    $bin = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    file_put_contents($path, $bin);
    return $path;
}

function expectOk(array $res): bool
{
    $s = $res['status'];
    if ($s < 200 || $s >= 300) {
        return false;
    }
    $b = $res['body'];
    if (is_array($b) && array_key_exists('success', $b)) {
        return (bool) $b['success'];
    }
    return true;
}

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pickdrop_api_test';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}
$img1 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'a.png');
$img2 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'b.png');
$img3 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'c.png');
$img4 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'd.png');
$img5 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'e.png');

$city = DB::table('cities')->where('id', 3)->first() ?: DB::table('cities')->first();
$areas = DB::table('areas')->where('city_id', $city->id)->orderBy('id')->take(2)->get();
$area1 = $areas[0] ?? null;
$area2 = $areas[1] ?? $area1;
$school = DB::table('schools')->first();

out("API BASE: {$base}");
out("City={$city->id}/{$city->name} Area1={$area1->id} Area2={$area2->id} School=" . ($school->id ?? 'n/a'));
out(str_repeat('-', 70));

// ---- PING ----
$r = api('GET', "{$base}/ping");
record($results, $pass, $fail, 'GET /ping', $r['status'], $r['body'], ($r['status'] === 200 && (($r['body']['status'] ?? '') === 'ok')));

// ---- PARENT REGISTER ----
$parentEmail = "parent.smoke.{$stamp}@test.local";
$parentPass = 'Secret123!';
$r = api('POST', "{$base}/parent/register", [
    'name' => 'Smoke Parent',
    'email' => $parentEmail,
    'password' => $parentPass,
    'password_confirmation' => $parentPass,
    'type' => 'parent',
    'address' => 'Test House Islamabad',
]);
$parentToken = is_array($r['body']) ? ($r['body']['data']['token'] ?? null) : null;
$parentUserId = is_array($r['body']) ? ($r['body']['data']['user']['id'] ?? null) : null;
record($results, $pass, $fail, 'POST /parent/register', $r['status'], $r['body'], expectOk($r) && $parentToken);

// verify email via DB code
if ($parentToken && $parentUserId) {
    $code = DB::table('email_verification_tokens')->where('user_id', $parentUserId)->whereNull('used_at')->value('code');
    $r = api('POST', "{$base}/parent/email/verify", ['code' => (string) $code], $parentToken);
    record($results, $pass, $fail, 'POST /parent/email/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $code);

    $r = api('GET', "{$base}/parent/me", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/me', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/cities", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/cities', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/cities/{$city->id}/areas", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/cities/{id}/areas', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/schools", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/schools', $r['status'], $r['body'], expectOk($r));

    // KYC multipart
    $cnic = sprintf('35202-%07d-1', random_int(1000000, 9999999));
    $multipart = [
        'date_of_birth' => '1990-05-15',
        'address' => 'Street 1, F-6 Islamabad',
        'city_id' => (string) $city->id,
        'cnic_number' => $cnic,
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
    ];
    $r = api('POST', "{$base}/parent/verification", null, $parentToken, $multipart);
    record($results, $pass, $fail, 'POST /parent/verification (KYC)', $r['status'], $r['body'], expectOk($r), $cnic);

    // Admin-approve KYC so onboarding can continue
    $kyc = ParentSelfVerification::where('user_id', $parentUserId)->latest('id')->first();
    if ($kyc) {
        $kyc->update(['status' => ParentSelfVerification::STATUS_APPROVED, 'reviewed_at' => now()]);
        out('[INFO] Parent KYC auto-approved in DB for further flow tests');
    }

    // Create student
    $studentPayload = [
        'name' => 'Smoke Kid',
        'grade' => '5',
        'school_id' => $school->id ?? null,
        'city_id' => $city->id,
        'pickup_area_id' => $area1->id,
        'pickup_location' => 'Home Gate F-6',
        'pickup_lat' => 33.7294,
        'pickup_lng' => 73.0931,
        'pickup_time' => '07:30',
        'dropoff_time' => '14:00',
        'emergency_name' => 'Uncle Smoke',
        'emergency_phone' => '03001234567',
        'emergency_relation' => 'uncle',
    ];
    $r = api('POST', "{$base}/parent/students", $studentPayload, $parentToken);
    $studentId = is_array($r['body']) ? ($r['body']['data']['student']['id'] ?? null) : null;
    record($results, $pass, $fail, 'POST /parent/students', $r['status'], $r['body'], expectOk($r) && $studentId);

    $r = api('GET', "{$base}/parent/students", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/students', $r['status'], $r['body'], expectOk($r));

    // Create pickup request
    if ($studentId) {
        $reqPayload = [
            'type' => 'parent',
            'service_type' => 'both',
            'student_id' => $studentId,
            'city_id' => $city->id,
            'area_id' => $area1->id,
            'drop_area_id' => $area2->id,
            'pickup_point' => 'Home Gate F-6',
            'pickup_lat' => 33.7294,
            'pickup_lng' => 73.0931,
            'drop_point' => 'School Gate',
            'drop_lat' => 33.7200,
            'drop_lng' => 73.0800,
            'pickup_time' => '07:30',
            'drop_time' => '08:15',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'duration_months' => 1,
            'shift_start_date' => date('Y-m-d', strtotime('+1 day')),
        ];
        $r = api('POST', "{$base}/parent/requests", $reqPayload, $parentToken);
        $parentRequestId = is_array($r['body']) ? ($r['body']['data']['request']['id'] ?? $r['body']['data']['id'] ?? null) : null;
        if (!$parentRequestId && is_array($r['body']['data'] ?? null)) {
            $parentRequestId = $r['body']['data']['pickup_request']['id'] ?? null;
        }
        record($results, $pass, $fail, 'POST /parent/requests', $r['status'], $r['body'], expectOk($r), 'req_id=' . ($parentRequestId ?: '?'));

        $r = api('GET', "{$base}/parent/requests", null, $parentToken);
        record($results, $pass, $fail, 'GET /parent/requests', $r['status'], $r['body'], expectOk($r));
    }

    $r = api('GET', "{$base}/parent/notifications", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/notifications', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/holidays", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/holidays', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/trips/recent", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/trips/recent', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/wallet", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/wallet', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/invoices", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/invoices', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/sos", [
        'lat' => 33.7294,
        'lng' => 73.0931,
        'message' => 'Smoke test SOS parent',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/sos', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/issues", [
        'subject' => 'Smoke issue',
        'description' => 'Automated smoke test issue',
        'type' => 'other',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/issues', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/device-token", [
        'token' => 'smoke-fcm-parent-' . $stamp,
        'platform' => 'android',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/device-token', $r['status'], $r['body'], expectOk($r));

    // Login again
    $r = api('POST', "{$base}/parent/login", ['email' => $parentEmail, 'password' => $parentPass]);
    $parentToken2 = is_array($r['body']) ? ($r['body']['data']['token'] ?? null) : null;
    record($results, $pass, $fail, 'POST /parent/login', $r['status'], $r['body'], expectOk($r) && $parentToken2);
}

// ---- SELF REGISTER ----
$selfEmail = "self.smoke.{$stamp}@test.local";
$selfPass = 'Secret123!';
$r = api('POST', "{$base}/self/register", [
    'name' => 'Smoke Self',
    'email' => $selfEmail,
    'password' => $selfPass,
    'password_confirmation' => $selfPass,
    'type' => 'self',
    'address' => 'Office Road Islamabad',
]);
$selfToken = is_array($r['body']) ? ($r['body']['data']['token'] ?? null) : null;
$selfUserId = is_array($r['body']) ? ($r['body']['data']['user']['id'] ?? null) : null;
record($results, $pass, $fail, 'POST /self/register', $r['status'], $r['body'], expectOk($r) && $selfToken);

if ($selfToken && $selfUserId) {
    $code = DB::table('email_verification_tokens')->where('user_id', $selfUserId)->whereNull('used_at')->value('code');
    $r = api('POST', "{$base}/self/email/verify", ['code' => (string) $code], $selfToken);
    record($results, $pass, $fail, 'POST /self/email/verify', $r['status'], $r['body'], expectOk($r));

    $cnic = sprintf('35202-%07d-3', random_int(1000000, 9999999));
    $multipart = [
        'date_of_birth' => '1992-08-20',
        'address' => 'Street 9, F-7 Islamabad',
        'city_id' => (string) $city->id,
        'cnic_number' => $cnic,
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
    ];
    $r = api('POST', "{$base}/self/verification", null, $selfToken, $multipart);
    record($results, $pass, $fail, 'POST /self/verification (KYC)', $r['status'], $r['body'], expectOk($r));

    $kyc = ParentSelfVerification::where('user_id', $selfUserId)->latest('id')->first();
    if ($kyc) {
        $kyc->update(['status' => ParentSelfVerification::STATUS_APPROVED, 'reviewed_at' => now()]);
        out('[INFO] Self KYC auto-approved in DB for further flow tests');
    }

    $r = api('POST', "{$base}/self/commute-profile", [
        'city_id' => $city->id,
        'pickup_area_id' => $area1->id,
        'pickup_point' => 'Home F-6',
        'pickup_lat' => 33.7294,
        'pickup_lng' => 73.0931,
        'office_name' => 'Blue Area Office',
        'drop_area_id' => $area2->id,
        'drop_point' => 'Blue Area Plaza',
        'drop_lat' => 33.7100,
        'drop_lng' => 73.0550,
        'pickup_time' => '08:00',
        'drop_time' => '09:00',
        'days' => ['monday', 'wednesday', 'friday'],
    ], $selfToken);
    record($results, $pass, $fail, 'POST /self/commute-profile', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/commute-profile", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/commute-profile', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/self/requests", [
        'type' => 'self',
        'service_type' => 'both',
        'city_id' => $city->id,
        'area_id' => $area1->id,
        'drop_area_id' => $area2->id,
        'pickup_point' => 'Home F-6',
        'pickup_lat' => 33.7294,
        'pickup_lng' => 73.0931,
        'drop_point' => 'Blue Area Plaza',
        'drop_lat' => 33.7100,
        'drop_lng' => 73.0550,
        'pickup_time' => '08:00',
        'drop_time' => '09:00',
        'days' => ['monday', 'wednesday', 'friday'],
        'duration_months' => 1,
        'shift_start_date' => date('Y-m-d', strtotime('+1 day')),
    ], $selfToken);
    record($results, $pass, $fail, 'POST /self/requests', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/requests", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/requests', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/me", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/me', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/self/login", ['email' => $selfEmail, 'password' => $selfPass]);
    record($results, $pass, $fail, 'POST /self/login', $r['status'], $r['body'], expectOk($r));
}

// ---- DRIVER REGISTER ----
$driverEmail = "driver.smoke.{$stamp}@test.local";
$driverPass = 'Secret123!';
$driverPhone = '03' . substr((string) (1000000000 + (int) substr($stamp, -8)), -9);
$r = api('POST', "{$base}/driver/register", [
    'name' => 'Smoke Driver',
    'phone' => $driverPhone,
    'email' => $driverEmail,
    'password' => $driverPass,
]);
$driverToken = is_array($r['body']) ? ($r['body']['data']['token'] ?? null) : null;
$driverUserId = is_array($r['body']) ? ($r['body']['data']['user']['id'] ?? null) : null;
record($results, $pass, $fail, 'POST /driver/register', $r['status'], $r['body'], expectOk($r) && $driverToken);

if ($driverToken && $driverUserId) {
    $otp = User::find($driverUserId)?->otp;
    $r = api('POST', "{$base}/driver/verify-email", [
        'email' => $driverEmail,
        'otp' => (string) $otp,
    ]);
    record($results, $pass, $fail, 'POST /driver/verify-email', $r['status'], $r['body'], expectOk($r), 'otp=' . $otp);

    // Skip phone gate for further KYC
    User::where('id', $driverUserId)->update(['phone_verified_at' => now()]);

    $r = api('GET', "{$base}/driver/me", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/me', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/cities", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/cities', $r['status'], $r['body'], expectOk($r));

    $cnic = sprintf('35202-%07d-5', random_int(1000000, 9999999));
    $multipart = [
        'date_of_birth' => '1988-01-10',
        'address' => 'Driver Street Islamabad',
        'city_id' => (string) $city->id,
        'service_areas[0]' => (string) $area1->id,
        'service_areas[1]' => (string) $area2->id,
        'cnic_number' => $cnic,
        'license_number' => 'LIC-' . $stamp,
        'license_expiry' => date('Y-m-d', strtotime('+2 years')),
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
        'license_front' => new CURLFile($img4, 'image/png', 'license_front.png'),
        'license_back' => new CURLFile($img5, 'image/png', 'license_back.png'),
    ];
    $r = api('POST', "{$base}/driver/verification", null, $driverToken, $multipart);
    record($results, $pass, $fail, 'POST /driver/verification (KYC)', $r['status'], $r['body'], expectOk($r));

    $dv = DriverVerification::where('user_id', $driverUserId)->latest('id')->first();
    if ($dv) {
        $dv->update(['status' => 'approved', 'reviewed_at' => now()]);
        User::where('id', $driverUserId)->update(['city_id' => $dv->city_id]);
        out('[INFO] Driver KYC auto-approved in DB');
    }

    // Vehicle verification - correct multipart field names
    $vehicleMultipart = [
        'vehicle_name' => 'Suzuki Alto',
        'vehicle_model' => '2020',
        'vehicle_color' => 'White',
        'license_plate' => 'ISB-' . substr($stamp, -4),
        'owner_name' => 'Smoke Driver',
        'owner_cnic_number' => sprintf('35202-%07d-7', random_int(1000000, 9999999)),
        'registration_card_front' => new CURLFile($img1, 'image/png', 'reg_f.png'),
        'registration_card_back' => new CURLFile($img2, 'image/png', 'reg_b.png'),
        'vehicle_front_photo' => new CURLFile($img3, 'image/png', 'veh_f.png'),
        'vehicle_back_photo' => new CURLFile($img4, 'image/png', 'veh_b.png'),
        'number_plate_photo' => new CURLFile($img5, 'image/png', 'plate.png'),
        'owner_document_front' => new CURLFile($img1, 'image/png', 'odf.png'),
        'owner_document_back' => new CURLFile($img2, 'image/png', 'odb.png'),
    ];
    $r = api('POST', "{$base}/driver/vehicle-verification", null, $driverToken, $vehicleMultipart);
    record($results, $pass, $fail, 'POST /driver/vehicle-verification', $r['status'], $r['body'], expectOk($r));

    $vv = DriverVehicleVerification::where('user_id', $driverUserId)->latest('id')->first();
    if ($vv) {
        $vv->update(['status' => 'approved', 'reviewed_at' => now()]);
        // Mirror admin approval side-effects
        \App\Models\Vehicle::updateOrCreate(
            ['driver_id' => $driverUserId],
            [
                'name' => $vv->vehicle_name,
                'license_plate' => $vv->license_plate,
                'vehicle_category_id' => $vv->vehicle_category_id,
                'status' => 'Active',
            ]
        );
        User::where('id', $driverUserId)->update(['status' => 'Active']);
        out('[INFO] Driver vehicle auto-approved + user Active');
    }

    $r = api('PUT', "{$base}/driver/me/duty", ['duty_status' => 'on_duty'], $driverToken);
    record($results, $pass, $fail, 'PUT /driver/me/duty', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/driver/location/update", ['lat' => 33.7294, 'lng' => 73.0931], $driverToken);
    record($results, $pass, $fail, 'POST /driver/location/update', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/requests/available", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/requests/available', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/requests/accepted", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/requests/accepted', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/rides", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/rides', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/rides/today", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/rides/today', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/earnings", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/earnings', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/notifications", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/notifications', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/payroll", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/payroll', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/driver/sos", [
        'lat' => 33.7294,
        'lng' => 73.0931,
        'message' => 'Smoke SOS driver',
    ], $driverToken);
    record($results, $pass, $fail, 'POST /driver/sos', $r['status'], $r['body'], expectOk($r));

    $r = api('PUT', "{$base}/driver/account/payment-details", [
        'bank_name' => 'HBL',
        'bank_account_title' => 'Smoke Driver',
        'bank_account_number' => '1234567890123',
        'bank_iban' => 'PK00HABB0000001234567890',
    ], $driverToken);
    record($results, $pass, $fail, 'PUT /driver/account/payment-details', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/account/payment-details", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/account/payment-details', $r['status'], $r['body'], expectOk($r));

    // Try accept parent request BEFORE login (login may revoke tokens)
    $pendingReq = DB::table('pickup_requests')
        ->where('parent_id', $parentUserId ?? 0)
        ->orderByDesc('id')
        ->first();
    if ($pendingReq) {
        $r = api('POST', "{$base}/driver/requests/{$pendingReq->id}/accept", [], $driverToken);
        record($results, $pass, $fail, 'POST /driver/requests/{id}/accept', $r['status'], $r['body'], expectOk($r), 'req=' . $pendingReq->id);
    }

    $r = api('POST', "{$base}/driver/login", ['email' => $driverEmail, 'password' => $driverPass]);
    $driverTokenAfterLogin = is_array($r['body']) ? ($r['body']['data']['token'] ?? null) : null;
    record($results, $pass, $fail, 'POST /driver/login', $r['status'], $r['body'], expectOk($r) && $driverTokenAfterLogin);
}

// ---- DB persistence check ----
out(str_repeat('-', 70));
out('DB PERSISTENCE CHECK');
$dbChecks = [
    'parent_user' => $parentUserId ? User::find($parentUserId) : null,
    'self_user' => $selfUserId ? User::find($selfUserId) : null,
    'driver_user' => $driverUserId ? User::find($driverUserId) : null,
    'students' => $parentUserId ? DB::table('students')->where('parent_id', $parentUserId)->count() : 0,
    'parent_requests' => $parentUserId ? DB::table('pickup_requests')->where('parent_id', $parentUserId)->count() : 0,
    'self_requests' => $selfUserId ? DB::table('pickup_requests')->where('parent_id', $selfUserId)->count() : 0,
    'parent_kyc' => $parentUserId ? ParentSelfVerification::where('user_id', $parentUserId)->count() : 0,
    'self_kyc' => $selfUserId ? ParentSelfVerification::where('user_id', $selfUserId)->count() : 0,
    'driver_kyc' => $driverUserId ? DriverVerification::where('user_id', $driverUserId)->count() : 0,
    'driver_vehicle' => $driverUserId ? DriverVehicleVerification::where('user_id', $driverUserId)->count() : 0,
    'commute' => $selfUserId ? DB::table('self_commute_profiles')->where('user_id', $selfUserId)->count() : 0,
    'sos' => DB::table('sos_alerts')->whereIn('user_id', array_filter([$parentUserId, $driverUserId]))->count(),
];
foreach ($dbChecks as $k => $v) {
    if (is_object($v)) {
        out("  {$k}: YES id={$v->id} email={$v->email} role={$v->role}");
    } else {
        out("  {$k}: {$v}");
    }
}

out(str_repeat('-', 70));
out("SUMMARY: PASS={$pass} FAIL={$fail} TOTAL=" . ($pass + $fail));
out("Test accounts:");
out("  Parent: {$parentEmail} / {$parentPass}");
out("  Self:   {$selfEmail} / {$selfPass}");
out("  Driver: {$driverEmail} / {$driverPass} phone={$driverPhone}");

$reportPath = __DIR__ . '/api_smoke_report_' . $stamp . '.json';
file_put_contents($reportPath, json_encode([
    'base' => $base,
    'pass' => $pass,
    'fail' => $fail,
    'accounts' => compact('parentEmail', 'selfEmail', 'driverEmail', 'parentPass', 'selfPass', 'driverPass', 'driverPhone'),
    'db' => array_map(fn ($v) => is_object($v) ? ['id' => $v->id, 'email' => $v->email, 'role' => $v->role] : $v, $dbChecks),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
out("Report: {$reportPath}");

exit($fail > 0 ? 1 : 0);
