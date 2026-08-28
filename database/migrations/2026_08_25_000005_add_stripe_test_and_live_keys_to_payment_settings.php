<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->string('stripe_mode', 10)->default('test')->after('stripe_enabled');
            $table->string('stripe_test_publishable_key')->nullable()->after('stripe_mode');
            $table->text('stripe_test_secret_key')->nullable()->after('stripe_test_publishable_key');
            $table->text('stripe_test_webhook_secret')->nullable()->after('stripe_test_secret_key');
            $table->string('stripe_live_publishable_key')->nullable()->after('stripe_test_webhook_secret');
            $table->text('stripe_live_secret_key')->nullable()->after('stripe_live_publishable_key');
            $table->text('stripe_live_webhook_secret')->nullable()->after('stripe_live_secret_key');
        });

        $rows = DB::table('payment_settings')->get();
        foreach ($rows as $row) {
            $publishable = (string) ($row->stripe_publishable_key ?? '');
            $isTest = str_contains($publishable, 'pk_test')
                || str_contains((string) ($row->stripe_secret_key ?? ''), 'sk_test');

            DB::table('payment_settings')->where('id', $row->id)->update([
                'stripe_mode' => $isTest ? 'test' : (filled($publishable) ? 'live' : 'test'),
                'stripe_test_publishable_key' => $isTest ? $row->stripe_publishable_key : null,
                'stripe_test_secret_key' => $isTest ? $row->stripe_secret_key : null,
                'stripe_test_webhook_secret' => $isTest ? $row->stripe_webhook_secret : null,
                'stripe_live_publishable_key' => $isTest ? null : $row->stripe_publishable_key,
                'stripe_live_secret_key' => $isTest ? null : $row->stripe_secret_key,
                'stripe_live_webhook_secret' => $isTest ? null : $row->stripe_webhook_secret,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_mode',
                'stripe_test_publishable_key',
                'stripe_test_secret_key',
                'stripe_test_webhook_secret',
                'stripe_live_publishable_key',
                'stripe_live_secret_key',
                'stripe_live_webhook_secret',
            ]);
        });
    }
};
