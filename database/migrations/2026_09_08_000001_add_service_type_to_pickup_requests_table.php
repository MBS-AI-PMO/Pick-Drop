<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('pickup_requests', 'service_type')) {
                $table->string('service_type', 20)->default('both')->after('type');
            }
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->string('pickup_point')->nullable()->change();
            $table->decimal('pickup_lat', 10, 7)->nullable()->change();
            $table->decimal('pickup_lng', 10, 7)->nullable()->change();
            $table->time('pickup_time')->nullable()->change();
            $table->string('drop_point')->nullable()->change();
            $table->decimal('drop_lat', 10, 7)->nullable()->change();
            $table->decimal('drop_lng', 10, 7)->nullable()->change();
            $table->time('drop_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });
    }
};
