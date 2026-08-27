<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_request_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
            $table->string('type', 20); // pickup | drop
            $table->unsignedInteger('sequence')->default(1);
            $table->string('name')->nullable();
            $table->string('point');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->time('scheduled_time');
            $table->string('status', 20)->default('pending'); // pending | done | skipped
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pickup_request_id', 'sequence']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_request_stops');
    }
};
