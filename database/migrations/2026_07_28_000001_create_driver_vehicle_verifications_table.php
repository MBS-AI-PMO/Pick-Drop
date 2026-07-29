<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_vehicle_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_category_id')->nullable()->constrained('vehicle_categories')->nullOnDelete();

            $table->string('vehicle_name');
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('license_plate', 50);

            $table->string('registration_card_front');
            $table->string('registration_card_back');
            $table->string('vehicle_front_photo');
            $table->string('vehicle_back_photo');
            $table->string('number_plate_photo');

            $table->string('owner_name');
            $table->string('owner_cnic_number', 20)->nullable();
            $table->string('owner_document_front');
            $table->string('owner_document_back');

            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique('user_id');
            $table->unique('license_plate');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_vehicle_verifications');
    }
};
