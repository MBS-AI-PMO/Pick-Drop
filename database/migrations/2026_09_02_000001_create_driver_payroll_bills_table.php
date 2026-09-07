<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payroll_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('scheduled_days')->default(0);
            $table->unsignedSmallInteger('present_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->unsignedSmallInteger('skipped_days')->default(0);
            $table->unsignedSmallInteger('holiday_days')->default(0);
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->decimal('calculated_amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, paid, rejected
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pickup_request_id', 'period_start', 'period_end'], 'payroll_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payroll_bills');
    }
};
