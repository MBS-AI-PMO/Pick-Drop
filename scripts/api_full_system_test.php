<?php

/**
 * Pick-Drop full system API test (Parent + Self + Driver).
 * Uses realistic Pakistani person names — not "smoke".
 *
 * Run: php scripts/api_full_system_test.php
 * Optional: set API_BASE=http://127.0.0.1:8000/api
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

// Distinct people for this run
$parent = [
    'name' => 'Ayesha Khan',
    'email' => "ayesha.khan.{$stamp}@pickdrop.test",
    'phone' => '0301' . substr($stamp, -7),
    'password' => 'Ayesha@2026',
    'address' => 'House 14, Street 7, F-8/1 Islamabad',
    'child' => 'Hassan Khan',
    'emergency' => ['name' => 'Imran Khan', 'phone' => '03025551234', 'relation' => 'father'],
];
$self = [
    'name' => 'Bilal Ahmed',
    'email' => "bilal.ahmed.{$stamp}@pickdrop.test",
    'phone' => '0302' . substr($stamp, -7),
    'password' => 'Bilal@2026',
    'address' => 'Flat 3B, G-11/2 Islamabad',
];
$driver = [
    'name' => 'Usman Ali',
    'email' => "usman.ali.{$stamp}@pickdrop.test",
    'phone' => '0303' . substr($stamp, -7),
    'password' => 'Usman@2026',
    'address' => 'Street 22, I-8/3 Islamabad',
];

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
    if (strlen($snippet) > 320) {
        $snippet = substr($snippet, 0, 320) . '...';
    }
    $results[] = compact('name', 'status', 'ok', 'note') + ['body' => $snippet];
    out(sprintf('[%s] %-48s HTTP %s %s', $icon, $name, $status, $note));
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);

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

function emailCode(int $userId): ?string
{
    return DB::table('email_verification_tokens')
        ->where('user_id', $userId)
        ->whereNull('used_at')
        ->orderByDesc('id')
        ->value('code');
}

function phoneCode(int $userId): ?string
{
    return User::query()->where('id', $userId)->value('phone_otp');
}

function dataId($body, string ...$paths): mixed
{
    if (!is_array($body)) {
        return null;
    }
    $data = $body['data'] ?? $body;
    foreach ($paths as $path) {
        $cur = $data;
        foreach (explode('.', $path) as $key) {
            if (!is_array($cur) || !array_key_exists($key, $cur)) {
                $cur = null;
                break;
            }
            $cur = $cur[$key];
        }
        if ($cur !== null) {
            return $cur;
        }
    }
    return null;
}

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pickdrop_full_test';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}
$img1 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'a.png');
$img2 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'b.png');
$img3 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'c.png');
$img4 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'd.png');
$img5 = tinyPng($tmpDir . DIRECTORY_SEPARATOR . 'e.png');

$city = DB::table('cities')->where('status', 'Active')->orderBy('id')->first()
    ?: DB::table('cities')->orderBy('id')->first();
$areas = DB::table('areas')->where('city_id', $city->id)->orderBy('id')->take(2)->get();
$area1 = $areas[0] ?? null;
$area2 = $areas[1] ?? $area1;
$school = DB::table('schools')->where('status', 'Active')->first()
    ?: DB::table('schools')->first();

$parentUserId = null;
$selfUserId = null;
$driverUserId = null;
$parentToken = null;
$selfToken = null;
$driverToken = null;
$parentRequestId = null;
$studentId = null;

out('Pick-Drop FULL SYSTEM TEST');
out("API BASE: {$base}");
out("City={$city->id}/{$city->name} Area1=" . ($area1->id ?? '?') . " Area2=" . ($area2->id ?? '?') . " School=" . ($school->id ?? 'n/a'));
out("People: {$parent['name']} | {$self['name']} | {$driver['name']}");
out(str_repeat('=', 72));

// ---- PING ----
$r = api('GET', "{$base}/ping");
record($results, $pass, $fail, 'GET /ping', $r['status'], $r['body'], ($r['status'] === 200 && (($r['body']['status'] ?? '') === 'ok')));

// =============================================================================
// PARENT: Ayesha Khan
// =============================================================================
out(str_repeat('-', 72));
out("PARENT FLOW — {$parent['name']}");

$r = api('POST', "{$base}/parent/register", [
    'name' => $parent['name'],
    'email' => $parent['email'],
    'contact' => $parent['phone'],
    'password' => $parent['password'],
    'password_confirmation' => $parent['password'],
    'type' => 'parent',
    'address' => $parent['address'],
]);
$parentToken = dataId($r['body'], 'token');
$parentUserId = dataId($r['body'], 'user.id');
record($results, $pass, $fail, 'POST /parent/register', $r['status'], $r['body'], expectOk($r) && $parentToken, $parent['email']);

if ($parentToken && $parentUserId) {
    $code = emailCode((int) $parentUserId);
    $r = api('POST', "{$base}/parent/email/verify", ['code' => (string) $code], $parentToken);
    record($results, $pass, $fail, 'POST /parent/email/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $code);

    $r = api('POST', "{$base}/parent/phone/verify/send", null, $parentToken);
    record($results, $pass, $fail, 'POST /parent/phone/verify/send', $r['status'], $r['body'], expectOk($r));

    $pCode = phoneCode((int) $parentUserId);
    $r = api('POST', "{$base}/parent/phone/verify", ['code' => (string) $pCode], $parentToken);
    record($results, $pass, $fail, 'POST /parent/phone/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $pCode);

    $r = api('GET', "{$base}/parent/verification", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/verification', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/me", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/me', $r['status'], $r['body'], expectOk($r));

    $r = api('PUT', "{$base}/parent/me", [
        'name' => $parent['name'],
        'address' => $parent['address'],
        'contact' => $parent['phone'],
        'emergency_contact_name' => $parent['emergency']['name'],
        'emergency_contact_phone' => $parent['emergency']['phone'],
    ], $parentToken);
    record($results, $pass, $fail, 'PUT /parent/me', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/cities", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/cities', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/cities/{$city->id}/areas", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/cities/{id}/areas', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/cities/{$city->id}/points", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/cities/{id}/points', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/schools", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/schools', $r['status'], $r['body'], expectOk($r));

    $cnic = sprintf('35202-%07d-1', random_int(1000000, 9999999));
    $multipart = [
        'father_name' => 'Tariq Khan',
        'date_of_birth' => '1989-04-12',
        'gender' => 'female',
        'nationality' => 'Pakistani',
        'address' => $parent['address'],
        'country' => 'Pakistan',
        'city_id' => (string) $city->id,
        'complete_address' => $parent['address'],
        'postal_code' => '44000',
        'cnic_number' => $cnic,
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
    ];
    $r = api('POST', "{$base}/parent/verification", null, $parentToken, $multipart);
    record($results, $pass, $fail, 'POST /parent/verification (KYC)', $r['status'], $r['body'], expectOk($r), $cnic);

    $r = api('GET', "{$base}/parent/verification", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/verification (after submit)', $r['status'], $r['body'], expectOk($r));

    $kyc = ParentSelfVerification::where('user_id', $parentUserId)->latest('id')->first();
    if ($kyc) {
        $kyc->update(['status' => ParentSelfVerification::STATUS_APPROVED, 'reviewed_at' => now()]);
        out('[INFO] Ayesha Khan KYC admin-approved in DB (simulate admin)');
    }

    $r = api('POST', "{$base}/parent/students", [
        'name' => $parent['child'],
        'grade' => '5',
        'school_id' => $school->id ?? null,
        'school_name' => $school->name ?? 'Islamabad Model School',
        'school_location' => 'F-7',
        'city_id' => $city->id,
        'pickup_area_id' => $area1->id,
        'pickup_location' => 'Home Gate F-8',
        'pickup_lat' => 33.7294,
        'pickup_lng' => 73.0931,
        'pickup_time' => '07:30',
        'dropoff_time' => '14:00',
        'emergency_name' => $parent['emergency']['name'],
        'emergency_phone' => $parent['emergency']['phone'],
        'emergency_relation' => $parent['emergency']['relation'],
    ], $parentToken);
    $studentId = dataId($r['body'], 'student.id', 'id');
    record($results, $pass, $fail, 'POST /parent/students', $r['status'], $r['body'], expectOk($r) && $studentId, 'child=' . $parent['child']);

    $r = api('GET', "{$base}/parent/students", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/students', $r['status'], $r['body'], expectOk($r));

    if ($studentId) {
        $r = api('GET', "{$base}/parent/students/{$studentId}", null, $parentToken);
        record($results, $pass, $fail, 'GET /parent/students/{id}', $r['status'], $r['body'], expectOk($r));

        $r = api('PUT', "{$base}/parent/students/{$studentId}", [
            'grade' => '6',
        ], $parentToken);
        record($results, $pass, $fail, 'PUT /parent/students/{id}', $r['status'], $r['body'], expectOk($r));

        $r = api('POST', "{$base}/parent/requests", [
            'type' => 'parent',
            'service_type' => 'both',
            'student_id' => $studentId,
            'city_id' => $city->id,
            'area_id' => $area1->id,
            'drop_area_id' => $area2->id,
            'pickup_point' => 'Home Gate F-8',
            'pickup_lat' => 33.7294,
            'pickup_lng' => 73.0931,
            'drop_point' => 'School Main Gate',
            'drop_lat' => 33.7200,
            'drop_lng' => 73.0800,
            'pickup_time' => '07:40',
            'drop_time' => '08:20',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'duration_months' => 1,
            'passenger_count' => 1,
            'shift_start_date' => date('Y-m-d', strtotime('+1 day')),
        ], $parentToken);
        $parentRequestId = dataId($r['body'], 'request.id', 'pickup_request.id', 'id');
        record($results, $pass, $fail, 'POST /parent/requests', $r['status'], $r['body'], expectOk($r), 'req_id=' . ($parentRequestId ?: '?'));

        $r = api('GET', "{$base}/parent/requests", null, $parentToken);
        record($results, $pass, $fail, 'GET /parent/requests', $r['status'], $r['body'], expectOk($r));

        if ($parentRequestId) {
            $r = api('GET', "{$base}/parent/requests/{$parentRequestId}", null, $parentToken);
            record($results, $pass, $fail, 'GET /parent/requests/{id}', $r['status'], $r['body'], expectOk($r));

            $r = api('GET', "{$base}/parent/requests/{$parentRequestId}/cancellation-preview", null, $parentToken);
            record($results, $pass, $fail, 'GET /parent/requests/{id}/cancellation-preview', $r['status'], $r['body'], expectOk($r));
        }
    }

    $r = api('GET', "{$base}/parent/notifications", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/notifications', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/notification-preferences", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/notification-preferences', $r['status'], $r['body'], expectOk($r));

    $r = api('PUT', "{$base}/parent/notification-preferences", [
        'push_enabled' => true,
        'email_enabled' => true,
        'new_messages' => true,
        'child_activity' => true,
        'school_alerts' => true,
        'payment_reminders' => true,
        'weekly_updates' => false,
        'promotions' => false,
    ], $parentToken);
    record($results, $pass, $fail, 'PUT /parent/notification-preferences', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/holidays", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/holidays', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/trips/recent", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/trips/recent', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/trips/today-status", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/trips/today-status', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/wallet", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/wallet', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/payment-methods", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/payment-methods', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/invoices", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/invoices', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/sos", [
        'pickup_request_id' => $parentRequestId,
        'lat' => 33.7294,
        'lng' => 73.0931,
        'message' => 'Ayesha Khan emergency test',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/sos', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/sos", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/sos', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/issues", [
        'pickup_request_id' => $parentRequestId,
        'subject' => 'Late pickup concern',
        'description' => 'Testing issue report for Hassan Khan route',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/issues', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/parent/issues", null, $parentToken);
    record($results, $pass, $fail, 'GET /parent/issues', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/device-token", [
        'token' => 'fcm-ayesha-' . $stamp,
        'platform' => 'android',
    ], $parentToken);
    record($results, $pass, $fail, 'POST /parent/device-token', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/parent/login", [
        'email' => $parent['email'],
        'password' => $parent['password'],
    ]);
    $parentToken = dataId($r['body'], 'token') ?: $parentToken;
    record($results, $pass, $fail, 'POST /parent/login', $r['status'], $r['body'], expectOk($r) && dataId($r['body'], 'token'));
}

// =============================================================================
// SELF: Bilal Ahmed
// =============================================================================
out(str_repeat('-', 72));
out("SELF FLOW — {$self['name']}");

$r = api('POST', "{$base}/self/register", [
    'name' => $self['name'],
    'email' => $self['email'],
    'contact' => $self['phone'],
    'password' => $self['password'],
    'password_confirmation' => $self['password'],
    'type' => 'self',
    'address' => $self['address'],
]);
$selfToken = dataId($r['body'], 'token');
$selfUserId = dataId($r['body'], 'user.id');
record($results, $pass, $fail, 'POST /self/register', $r['status'], $r['body'], expectOk($r) && $selfToken, $self['email']);

if ($selfToken && $selfUserId) {
    $code = emailCode((int) $selfUserId);
    $r = api('POST', "{$base}/self/email/verify", ['code' => (string) $code], $selfToken);
    record($results, $pass, $fail, 'POST /self/email/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $code);

    $r = api('POST', "{$base}/self/phone/verify/send", null, $selfToken);
    record($results, $pass, $fail, 'POST /self/phone/verify/send', $r['status'], $r['body'], expectOk($r));

    $pCode = phoneCode((int) $selfUserId);
    $r = api('POST', "{$base}/self/phone/verify", ['code' => (string) $pCode], $selfToken);
    record($results, $pass, $fail, 'POST /self/phone/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $pCode);

    $r = api('GET', "{$base}/self/verification", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/verification', $r['status'], $r['body'], expectOk($r));

    $cnic = sprintf('35202-%07d-3', random_int(1000000, 9999999));
    $multipart = [
        'father_name' => 'Naveed Ahmed',
        'date_of_birth' => '1993-09-18',
        'gender' => 'male',
        'nationality' => 'Pakistani',
        'address' => $self['address'],
        'country' => 'Pakistan',
        'city_id' => (string) $city->id,
        'complete_address' => $self['address'],
        'postal_code' => '44000',
        'cnic_number' => $cnic,
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
    ];
    $r = api('POST', "{$base}/self/verification", null, $selfToken, $multipart);
    record($results, $pass, $fail, 'POST /self/verification (KYC)', $r['status'], $r['body'], expectOk($r), $cnic);

    $kyc = ParentSelfVerification::where('user_id', $selfUserId)->latest('id')->first();
    if ($kyc) {
        $kyc->update(['status' => ParentSelfVerification::STATUS_APPROVED, 'reviewed_at' => now()]);
        out('[INFO] Bilal Ahmed KYC admin-approved in DB (simulate admin)');
    }

    $r = api('POST', "{$base}/self/commute-profile", [
        'city_id' => $city->id,
        'pickup_area_id' => $area1->id,
        'pickup_point' => 'Home G-11',
        'pickup_lat' => 33.6680,
        'pickup_lng' => 73.0480,
        'office_name' => 'Blue Area Tech Hub',
        'drop_area_id' => $area2->id,
        'drop_point' => 'Blue Area Plaza Gate',
        'drop_lat' => 33.7100,
        'drop_lng' => 73.0550,
        'pickup_time' => '08:00',
        'drop_time' => '09:00',
        'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
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
        'pickup_point' => 'Home G-11',
        'pickup_lat' => 33.6680,
        'pickup_lng' => 73.0480,
        'drop_point' => 'Blue Area Plaza Gate',
        'drop_lat' => 33.7100,
        'drop_lng' => 73.0550,
        'pickup_time' => '08:00',
        'drop_time' => '09:00',
        'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'duration_months' => 1,
        'passenger_count' => 1,
        'shift_start_date' => date('Y-m-d', strtotime('+1 day')),
    ], $selfToken);
    $selfRequestId = dataId($r['body'], 'request.id', 'pickup_request.id', 'id');
    record($results, $pass, $fail, 'POST /self/requests', $r['status'], $r['body'], expectOk($r), 'req_id=' . ($selfRequestId ?: '?'));

    $r = api('GET', "{$base}/self/requests", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/requests', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/me", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/me', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/wallet", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/wallet', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/invoices", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/invoices', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/self/holidays", null, $selfToken);
    record($results, $pass, $fail, 'GET /self/holidays', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/self/sos", [
        'lat' => 33.6680,
        'lng' => 73.0480,
        'message' => 'Bilal Ahmed SOS test',
    ], $selfToken);
    record($results, $pass, $fail, 'POST /self/sos', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/self/device-token", [
        'token' => 'fcm-bilal-' . $stamp,
        'platform' => 'ios',
    ], $selfToken);
    record($results, $pass, $fail, 'POST /self/device-token', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/self/login", [
        'email' => $self['email'],
        'password' => $self['password'],
    ]);
    $selfToken = dataId($r['body'], 'token') ?: $selfToken;
    record($results, $pass, $fail, 'POST /self/login', $r['status'], $r['body'], expectOk($r) && dataId($r['body'], 'token'));
}

// =============================================================================
// DRIVER: Usman Ali
// =============================================================================
out(str_repeat('-', 72));
out("DRIVER FLOW — {$driver['name']}");

$r = api('POST', "{$base}/driver/register", [
    'name' => $driver['name'],
    'phone' => $driver['phone'],
    'email' => $driver['email'],
    'password' => $driver['password'],
]);
$driverToken = dataId($r['body'], 'token');
$driverUserId = dataId($r['body'], 'user.id');
record($results, $pass, $fail, 'POST /driver/register', $r['status'], $r['body'], expectOk($r) && $driverToken, $driver['email']);

if ($driverToken && $driverUserId) {
    $otp = User::find($driverUserId)?->otp ?: emailCode((int) $driverUserId);
    $r = api('POST', "{$base}/driver/verify-email", [
        'email' => $driver['email'],
        'otp' => (string) $otp,
    ]);
    record($results, $pass, $fail, 'POST /driver/verify-email', $r['status'], $r['body'], expectOk($r), 'otp=' . $otp);

    $r = api('POST', "{$base}/driver/phone/verify/send", null, $driverToken);
    record($results, $pass, $fail, 'POST /driver/phone/verify/send', $r['status'], $r['body'], expectOk($r));

    $pCode = phoneCode((int) $driverUserId);
    $r = api('POST', "{$base}/driver/phone/verify", ['code' => (string) $pCode], $driverToken);
    record($results, $pass, $fail, 'POST /driver/phone/verify', $r['status'], $r['body'], expectOk($r), 'code=' . $pCode);

    $r = api('GET', "{$base}/driver/verification", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/verification', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/me", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/me', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/cities", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/cities', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/cities/{$city->id}/areas", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/cities/{id}/areas', $r['status'], $r['body'], expectOk($r));

    $cnic = sprintf('35202-%07d-5', random_int(1000000, 9999999));
    $multipart = [
        'father_name' => 'Asghar Ali',
        'date_of_birth' => '1987-02-25',
        'address' => $driver['address'],
        'city_id' => (string) $city->id,
        'service_areas[0]' => (string) $area1->id,
        'service_areas[1]' => (string) $area2->id,
        'cnic_number' => $cnic,
        'license_number' => 'ICT-DL-' . substr($stamp, -6),
        'license_expiry' => date('Y-m-d', strtotime('+2 years')),
        'terms_accepted' => '1',
        'cnic_front' => new CURLFile($img1, 'image/png', 'cnic_front.png'),
        'cnic_back' => new CURLFile($img2, 'image/png', 'cnic_back.png'),
        'selfie_photo' => new CURLFile($img3, 'image/png', 'selfie.png'),
        'license_front' => new CURLFile($img4, 'image/png', 'license_front.png'),
        'license_back' => new CURLFile($img5, 'image/png', 'license_back.png'),
    ];
    $r = api('POST', "{$base}/driver/verification", null, $driverToken, $multipart);
    record($results, $pass, $fail, 'POST /driver/verification (KYC)', $r['status'], $r['body'], expectOk($r), $cnic);

    $dv = DriverVerification::where('user_id', $driverUserId)->latest('id')->first();
    if ($dv) {
        $dv->update(['status' => 'approved', 'reviewed_at' => now()]);
        User::where('id', $driverUserId)->update(['city_id' => $dv->city_id]);
        out('[INFO] Usman Ali driver KYC admin-approved in DB');
    }

    $r = api('GET', "{$base}/driver/vehicle-verification", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/vehicle-verification', $r['status'], $r['body'], expectOk($r));

    $vehicleMultipart = [
        'vehicle_name' => 'Suzuki WagonR',
        'vehicle_model' => '2021',
        'vehicle_color' => 'Silver',
        'license_plate' => 'ISB-' . substr($stamp, -4),
        'owner_name' => $driver['name'],
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
        out('[INFO] Usman Ali vehicle approved + account Active');
    }

    $weekMorningHours = [
        ['day' => 'monday', 'start' => '07:00', 'end' => '10:00'],
        ['day' => 'tuesday', 'start' => '07:00', 'end' => '10:00'],
        ['day' => 'wednesday', 'start' => '07:00', 'end' => '10:00'],
        ['day' => 'thursday', 'start' => '07:00', 'end' => '10:00'],
        ['day' => 'friday', 'start' => '07:00', 'end' => '10:00'],
    ];

    $r = api('PUT', "{$base}/driver/me", [
        'name' => $driver['name'],
        'home_address' => $driver['address'],
        'city_id' => $city->id,
        'service_areas' => [$area1->id, $area2->id],
        'available_seats' => 3,
        'availability_hours' => $weekMorningHours,
        'emergency_contact_name' => 'Saima Ali',
        'emergency_contact_phone' => '03098887766',
    ], $driverToken);
    record($results, $pass, $fail, 'PUT /driver/me', $r['status'], $r['body'], expectOk($r));

    $r = api('PUT', "{$base}/driver/me/duty", ['duty_status' => 'on_duty'], $driverToken);
    record($results, $pass, $fail, 'PUT /driver/me/duty', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/driver/location/update", ['lat' => 33.7294, 'lng' => 73.0931], $driverToken);
    record($results, $pass, $fail, 'POST /driver/location/update', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/driver/service-areas", [
        'city_id' => $city->id,
        'area_ids' => [$area1->id, $area2->id],
        'available_seats' => 3,
        'availability_hours' => $weekMorningHours,
    ], $driverToken);
    record($results, $pass, $fail, 'POST /driver/service-areas', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/service-areas", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/service-areas', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/requests/available", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/requests/available', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/requests/accepted", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/requests/accepted', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/requests/cover", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/requests/cover', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/rides", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/rides', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/rides/today", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/rides/today', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/earnings", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/earnings', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/notifications", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/notifications', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/holidays", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/holidays', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/payroll", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/payroll', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/payrolls", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/payrolls', $r['status'], $r['body'], expectOk($r));

    $r = api('PUT', "{$base}/driver/account/payment-details", [
        'bank_name' => 'HBL',
        'bank_account_title' => $driver['name'],
        'bank_account_number' => '1234567890123',
        'bank_iban' => 'PK00HABB0000001234567890',
    ], $driverToken);
    record($results, $pass, $fail, 'PUT /driver/account/payment-details', $r['status'], $r['body'], expectOk($r));

    $r = api('GET', "{$base}/driver/account/payment-details", null, $driverToken);
    record($results, $pass, $fail, 'GET /driver/account/payment-details', $r['status'], $r['body'], expectOk($r));

    $r = api('POST', "{$base}/driver/device-token", [
        'token' => 'fcm-usman-' . $stamp,
        'platform' => 'android',
    ], $driverToken);
    record($results, $pass, $fail, 'POST /driver/device-token', $r['status'], $r['body'], expectOk($r));

    // SOS without assigned request (allowed)
    $r = api('POST', "{$base}/driver/sos", [
        'lat' => 33.7294,
        'lng' => 73.0931,
        'message' => 'Usman Ali SOS test',
    ], $driverToken);
    record($results, $pass, $fail, 'POST /driver/sos', $r['status'], $r['body'], expectOk($r));

    // Accept Ayesha's request if available
    $pendingReq = $parentRequestId
        ? DB::table('pickup_requests')->where('id', $parentRequestId)->first()
        : DB::table('pickup_requests')->where('parent_id', $parentUserId ?? 0)->orderByDesc('id')->first();

    $acceptedOk = false;
    if ($pendingReq) {
        $r = api('POST', "{$base}/driver/requests/{$pendingReq->id}/accept", [], $driverToken);
        $acceptedOk = expectOk($r);
        record($results, $pass, $fail, 'POST /driver/requests/{id}/accept', $r['status'], $r['body'], $acceptedOk, 'req=' . $pendingReq->id);

        if ($acceptedOk) {
            $r = api('GET', "{$base}/parent/requests/{$pendingReq->id}/driver", null, $parentToken);
            record($results, $pass, $fail, 'GET /parent/requests/{id}/driver', $r['status'], $r['body'], expectOk($r));

            $r = api('GET', "{$base}/parent/requests/{$pendingReq->id}/contact", null, $parentToken);
            record($results, $pass, $fail, 'GET /parent/requests/{id}/contact', $r['status'], $r['body'], expectOk($r));

            $r = api('GET', "{$base}/driver/requests/{$pendingReq->id}/contact", null, $driverToken);
            record($results, $pass, $fail, 'GET /driver/requests/{id}/contact', $r['status'], $r['body'], expectOk($r));

            $r = api('POST', "{$base}/parent/requests/{$pendingReq->id}/messages", [
                'message' => 'Assalam o Alaikum Usman bhai, Hassan ready hoga 7:35 pe.',
            ], $parentToken);
            record($results, $pass, $fail, 'POST /parent/requests/{id}/messages', $r['status'], $r['body'], expectOk($r));

            $r = api('POST', "{$base}/driver/requests/{$pendingReq->id}/messages", [
                'message' => 'Ji Ayesha madam, main 7:30 pe gate pe honga.',
            ], $driverToken);
            record($results, $pass, $fail, 'POST /driver/requests/{id}/messages', $r['status'], $r['body'], expectOk($r));

            $r = api('GET', "{$base}/parent/requests/{$pendingReq->id}/messages", null, $parentToken);
            record($results, $pass, $fail, 'GET /parent/requests/{id}/messages', $r['status'], $r['body'], expectOk($r));

            // Issue after assignment (must belong to this driver)
            $r = api('POST', "{$base}/driver/issues", [
                'pickup_request_id' => $pendingReq->id,
                'type' => 'delay',
                'reason' => 'Heavy traffic near F-8',
                'eta_change' => 12,
            ], $driverToken);
            record($results, $pass, $fail, 'POST /driver/issues', $r['status'], $r['body'], expectOk($r));
        }
    }

    $r = api('POST', "{$base}/driver/login", [
        'email' => $driver['email'],
        'password' => $driver['password'],
    ]);
    record($results, $pass, $fail, 'POST /driver/login', $r['status'], $r['body'], expectOk($r) && dataId($r['body'], 'token'));
}

// ---- DB CHECK ----
out(str_repeat('=', 72));
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
    'messages' => $parentRequestId ? DB::table('driver_messages')->where('pickup_request_id', $parentRequestId)->count() : 0,
    'sos' => DB::table('sos_alerts')->whereIn('user_id', array_filter([$parentUserId, $selfUserId, $driverUserId]))->count(),
];
foreach ($dbChecks as $k => $v) {
    if (is_object($v)) {
        out("  {$k}: YES id={$v->id} name={$v->name} email={$v->email} role={$v->role}");
    } else {
        out("  {$k}: {$v}");
    }
}

out(str_repeat('=', 72));
out("SUMMARY: PASS={$pass} FAIL={$fail} TOTAL=" . ($pass + $fail));
out('Test people / accounts:');
out("  Parent: {$parent['name']} <{$parent['email']}> / {$parent['password']} phone={$parent['phone']}");
out("  Self:   {$self['name']} <{$self['email']}> / {$self['password']} phone={$self['phone']}");
out("  Driver: {$driver['name']} <{$driver['email']}> / {$driver['password']} phone={$driver['phone']}");

$reportPath = __DIR__ . '/api_full_system_report_' . $stamp . '.json';
file_put_contents($reportPath, json_encode([
    'base' => $base,
    'pass' => $pass,
    'fail' => $fail,
    'people' => [
        'parent' => $parent,
        'self' => $self,
        'driver' => $driver,
    ],
    'db' => array_map(fn ($v) => is_object($v) ? ['id' => $v->id, 'name' => $v->name, 'email' => $v->email, 'role' => $v->role] : $v, $dbChecks),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
out("Report: {$reportPath}");

exit($fail > 0 ? 1 : 0);
