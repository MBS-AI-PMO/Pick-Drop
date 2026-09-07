<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_payrolls', 'upcoming_days')) {
                $table->unsignedInteger('upcoming_days')->default(0)->after('parent_skip_days');
            }
            if (! Schema::hasColumn('driver_payrolls', 'expected_net')) {
                $table->decimal('expected_net', 12, 2)->default(0)->after('net');
            }
        });

        Schema::table('driver_payroll_items', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_payroll_items', 'upcoming_days')) {
                $table->unsignedInteger('upcoming_days')->default(0)->after('parent_skip_days');
            }
            if (! Schema::hasColumn('driver_payroll_items', 'expected_net')) {
                $table->decimal('expected_net', 12, 2)->default(0)->after('net');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('driver_payrolls', 'upcoming_days')) {
                $table->dropColumn('upcoming_days');
            }
            if (Schema::hasColumn('driver_payrolls', 'expected_net')) {
                $table->dropColumn('expected_net');
            }
        });

        Schema::table('driver_payroll_items', function (Blueprint $table) {
            if (Schema::hasColumn('driver_payroll_items', 'upcoming_days')) {
                $table->dropColumn('upcoming_days');
            }
            if (Schema::hasColumn('driver_payroll_items', 'expected_net')) {
                $table->dropColumn('expected_net');
            }
        });
    }
};
