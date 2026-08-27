<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_months')->default(1)->after('days');
            $table->date('shift_start_date')->nullable()->after('duration_months');
            $table->date('shift_end_date')->nullable()->after('shift_start_date');
            $table->decimal('distance_km', 8, 2)->nullable()->after('shift_end_date');
            $table->unsignedInteger('trip_count')->nullable()->after('distance_km');
            $table->decimal('estimated_amount', 12, 2)->nullable()->after('trip_count');
            $table->string('payment_status', 30)->default('unpaid')->after('estimated_amount');
        });

        DB::table('pickup_requests')
            ->whereIn('status', ['accepted', 'picked_up', 'dropped', 'completed'])
            ->update(['payment_status' => 'paid']);

        DB::table('pickup_requests')
            ->whereNull('shift_start_date')
            ->update([
                'shift_start_date' => DB::raw('COALESCE(scheduled_date, DATE(created_at))'),
            ]);
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn([
                'duration_months',
                'shift_start_date',
                'shift_end_date',
                'distance_km',
                'trip_count',
                'estimated_amount',
                'payment_status',
            ]);
        });
    }
};
