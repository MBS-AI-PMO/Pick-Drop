<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_vehicle_verifications', function (Blueprint $table) {
            $table->string('owner_document_front')->nullable()->change();
            $table->string('owner_document_back')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('driver_vehicle_verifications', function (Blueprint $table) {
            $table->string('owner_document_front')->nullable(false)->change();
            $table->string('owner_document_back')->nullable(false)->change();
        });
    }
};
