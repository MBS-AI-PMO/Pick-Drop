<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g. 'Bus', 'Van'
            $table->integer('capacity')->nullable(); // e.g. 28, 40
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_settings');
    }
};
