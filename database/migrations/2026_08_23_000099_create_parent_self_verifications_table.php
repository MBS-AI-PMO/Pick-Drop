<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parent_self_verifications')) {
            return;
        }

        Schema::create('parent_self_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_type', 20)->default('parent');
            $table->string('full_name');
            $table->string('father_name')->nullable();
            $table->date('date_of_birth');
            $table->string('gender', 20)->nullable();
            $table->string('nationality', 100);
            $table->string('address', 500)->nullable();
            $table->string('country', 100);
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('city_name')->nullable();
            $table->string('complete_address', 1000);
            $table->string('postal_code', 30)->nullable();
            $table->string('cnic_number', 20);
            $table->string('cnic_front');
            $table->string('cnic_back');
            $table->string('selfie_photo');
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->unique('cnic_number');
            $table->index('status');
            $table->index('account_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_self_verifications');
    }
};
