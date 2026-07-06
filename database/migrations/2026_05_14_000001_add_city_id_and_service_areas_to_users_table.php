<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('details')->constrained('cities')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'service_areas')) {
                $table->json('service_areas')->nullable()->after('city_id');
            }
        });

        if (Schema::hasColumn('users', 'areas')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('areas');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'service_areas')) {
                $table->dropColumn('service_areas');
            }
            if (Schema::hasColumn('users', 'city_id')) {
                $table->dropConstrainedForeignId('city_id');
            }
        });
    }
};
