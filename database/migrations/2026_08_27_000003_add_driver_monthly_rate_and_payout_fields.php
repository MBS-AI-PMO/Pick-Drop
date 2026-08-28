<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pick_drop_charges', function (Blueprint $table) {
            $table->decimal('driver_monthly_rate', 12, 2)->default(0)->after('per_km_rate');
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->decimal('driver_monthly_rate', 12, 2)->nullable()->after('estimated_amount');
            $table->decimal('driver_payout_amount', 12, 2)->nullable()->after('driver_monthly_rate');
            $table->string('driver_payout_status', 30)->default('unpaid')->after('driver_payout_amount');
            $table->date('driver_payout_due_on')->nullable()->after('driver_payout_status');
            $table->timestamp('driver_payout_paid_at')->nullable()->after('driver_payout_due_on');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn([
                'driver_monthly_rate',
                'driver_payout_amount',
                'driver_payout_status',
                'driver_payout_due_on',
                'driver_payout_paid_at',
            ]);
        });

        Schema::table('pick_drop_charges', function (Blueprint $table) {
            $table->dropColumn('driver_monthly_rate');
        });
    }
};
