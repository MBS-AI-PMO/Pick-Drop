<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shift_day_runs')) {
            Schema::table('shift_day_runs', function (Blueprint $table) {
                if (! Schema::hasColumn('shift_day_runs', 'ended_at')) {
                    $table->timestamp('ended_at')->nullable()->after('arrived_at');
                }
                if (! Schema::hasColumn('shift_day_runs', 'ended_by')) {
                    $table->string('ended_by', 20)->nullable()->after('ended_at');
                }
                if (! Schema::hasColumn('shift_day_runs', 'emergency_locked')) {
                    $table->boolean('emergency_locked')->default(false)->after('ended_by');
                }
            });
        }

        if (Schema::hasTable('shift_day_stop_logs') && ! Schema::hasColumn('shift_day_stop_logs', 'reason')) {
            Schema::table('shift_day_stop_logs', function (Blueprint $table) {
                $table->string('reason')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('sos_alerts') && ! Schema::hasColumn('sos_alerts', 'type')) {
            Schema::table('sos_alerts', function (Blueprint $table) {
                $table->string('type', 30)->nullable()->after('pickup_request_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shift_day_runs')) {
            Schema::table('shift_day_runs', function (Blueprint $table) {
                foreach (['emergency_locked', 'ended_by', 'ended_at'] as $column) {
                    if (Schema::hasColumn('shift_day_runs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('shift_day_stop_logs') && Schema::hasColumn('shift_day_stop_logs', 'reason')) {
            Schema::table('shift_day_stop_logs', function (Blueprint $table) {
                $table->dropColumn('reason');
            });
        }

        if (Schema::hasTable('sos_alerts') && Schema::hasColumn('sos_alerts', 'type')) {
            Schema::table('sos_alerts', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
