<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_commute_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();

            $table->foreignId('pickup_area_id')->constrained('areas')->restrictOnDelete();
            $table->string('pickup_point');
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);

            $table->string('office_name')->nullable();
            $table->foreignId('drop_area_id')->constrained('areas')->restrictOnDelete();
            $table->string('drop_point');
            $table->decimal('drop_lat', 10, 7);
            $table->decimal('drop_lng', 10, 7);

            $table->time('pickup_time');
            $table->time('drop_time');
            $table->json('days')->nullable();

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_commute_profiles');
    }
};
