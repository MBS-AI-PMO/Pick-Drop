<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PickDrop');
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_address', 500)->nullable();
            $table->string('invoice_prefix', 20)->default('INV');
            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->boolean('bank_enabled')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_title')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_swift')->nullable();
            $table->string('bank_branch')->nullable();

            $table->boolean('stripe_enabled')->default(false);
            $table->string('stripe_publishable_key')->nullable();
            $table->text('stripe_secret_key')->nullable();
            $table->text('stripe_webhook_secret')->nullable();
            $table->string('stripe_currency', 10)->default('pkr');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
