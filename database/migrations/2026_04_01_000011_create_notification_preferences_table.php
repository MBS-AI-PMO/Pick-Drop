<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);

            $table->boolean('new_messages')->default(true);
            $table->boolean('child_activity')->default(true);
            $table->boolean('school_alerts')->default(true);

            $table->boolean('payment_reminders')->default(true);
            $table->boolean('weekly_updates')->default(false);
            $table->boolean('promotions')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};

