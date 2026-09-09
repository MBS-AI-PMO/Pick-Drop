<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table) {
                if (! Schema::hasColumn('routes', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('vehicle_id')->constrained('schools')->nullOnDelete();
                }
                if (! Schema::hasColumn('routes', 'driver_id')) {
                    $table->foreignId('driver_id')->nullable()->after('school_id')->constrained('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('route_stops')) {
            Schema::table('route_stops', function (Blueprint $table) {
                if (! Schema::hasColumn('route_stops', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('name');
                }
                if (! Schema::hasColumn('route_stops', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
                if (! Schema::hasColumn('route_stops', 'address')) {
                    $table->string('address')->nullable()->after('longitude');
                }
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'duty_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('duty_status', 20)->default('on_duty')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'duty_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('duty_status');
            });
        }

        if (Schema::hasTable('route_stops')) {
            Schema::table('route_stops', function (Blueprint $table) {
                foreach (['address', 'longitude', 'latitude'] as $column) {
                    if (Schema::hasColumn('route_stops', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table) {
                if (Schema::hasColumn('routes', 'driver_id')) {
                    $table->dropConstrainedForeignId('driver_id');
                }
                if (Schema::hasColumn('routes', 'school_id')) {
                    $table->dropConstrainedForeignId('school_id');
                }
            });
        }
    }
};
