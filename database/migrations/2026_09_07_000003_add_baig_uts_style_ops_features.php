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
                if (! Schema::hasColumn('shift_day_runs', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('arrival_notified_at');
                }
                if (! Schema::hasColumn('shift_day_runs', 'arrived_at')) {
                    $table->timestamp('arrived_at')->nullable()->after('started_at');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasColumn('students', 'emergency_name')) {
                    $table->string('emergency_name')->nullable()->after('status');
                }
                if (! Schema::hasColumn('students', 'emergency_phone')) {
                    $table->string('emergency_phone', 30)->nullable()->after('emergency_name');
                }
                if (! Schema::hasColumn('students', 'emergency_relation')) {
                    $table->string('emergency_relation', 50)->nullable()->after('emergency_phone');
                }
            });
        }

        if (! Schema::hasTable('location_points')) {
            Schema::create('location_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->string('name');
                $table->string('type', 20)->default('both'); // pickup | drop | both
                $table->string('address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->index(['city_id', 'type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('location_points');

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                foreach (['emergency_relation', 'emergency_phone', 'emergency_name'] as $column) {
                    if (Schema::hasColumn('students', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('shift_day_runs')) {
            Schema::table('shift_day_runs', function (Blueprint $table) {
                foreach (['arrived_at', 'started_at'] as $column) {
                    if (Schema::hasColumn('shift_day_runs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
