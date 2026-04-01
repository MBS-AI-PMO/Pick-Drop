<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('parent'); // parent/self
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();

            $table->string('pickup_point');
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);

            $table->string('drop_point');
            $table->decimal('drop_lat', 10, 7);
            $table->decimal('drop_lng', 10, 7);

            $table->time('pickup_time');
            $table->time('drop_time');
            $table->json('days')->nullable(); // ["mon","tue",...]

            $table->date('scheduled_date')->nullable(); // optional for one-time trips

            $table->string('status')->default('pending'); // pending/accepted/picked_up/dropped/completed/cancelled
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_requests');
    }
};

