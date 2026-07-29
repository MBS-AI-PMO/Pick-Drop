<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Personal details
            $table->string('full_name');
            $table->string('father_name')->nullable();
            $table->date('date_of_birth');
            $table->string('address', 500);
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();

            // Identity (CNIC)
            $table->string('cnic_number', 20);
            $table->string('cnic_front');
            $table->string('cnic_back');
            $table->string('selfie_photo');

            // Driving license
            $table->string('license_number', 50);
            $table->string('license_front');
            $table->string('license_back');
            $table->date('license_expiry');

            // Agreement
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();

            // Admin review
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique('user_id');
            $table->unique('cnic_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_verifications');
    }
};
