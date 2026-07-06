<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
     Schema::create('driver_pickup_request_rejections', function (Blueprint $table) {
        $table->id();

        $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();

        $table->timestamps();

       $table->unique(
        ['driver_id', 'pickup_request_id'],
        'driver_pickup_unique'
       );
});
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_pickup_request_rejections');
    }
};
