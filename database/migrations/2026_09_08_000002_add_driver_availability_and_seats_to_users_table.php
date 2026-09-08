<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'available_seats')) {
                $table->unsignedTinyInteger('available_seats')->nullable()->after('service_areas');
            }
            if (! Schema::hasColumn('users', 'availability_hours')) {
                $table->json('availability_hours')->nullable()->after('available_seats');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'availability_hours')) {
                $table->dropColumn('availability_hours');
            }
            if (Schema::hasColumn('users', 'available_seats')) {
                $table->dropColumn('available_seats');
            }
        });
    }
};
