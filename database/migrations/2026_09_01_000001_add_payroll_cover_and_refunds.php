<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('pickup_requests', 'round_trip')) {
                $table->boolean('round_trip')->default(true)->after('trip_count');
            }
        });

        Schema::table('pickup_request_stops', function (Blueprint $table) {
            if (! Schema::hasColumn('pickup_request_stops', 'leg')) {
                $table->string('leg', 20)->default('outbound')->after('type');
            }
        });

        Schema::table('shift_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_attendances', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('shift_attendances', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
            }
            if (! Schema::hasColumn('shift_attendances', 'refund_reason')) {
                $table->string('refund_reason')->nullable()->after('refunded_at');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'pickup_request_id')) {
                $table->foreignId('pickup_request_id')->nullable()->after('referred_user_id')->constrained('pickup_requests')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('driver_payrolls')) {
            Schema::create('driver_payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
                $table->string('month', 7);
                $table->unsignedInteger('scheduled_days')->default(0);
                $table->unsignedInteger('worked_days')->default(0);
                $table->unsignedInteger('leave_days')->default(0);
                $table->unsignedInteger('absent_days')->default(0);
                $table->unsignedInteger('holiday_days')->default(0);
                $table->unsignedInteger('parent_skip_days')->default(0);
                $table->decimal('daily_rate', 12, 2)->default(0);
                $table->decimal('gross', 12, 2)->default(0);
                $table->decimal('deduction', 12, 2)->default(0);
                $table->decimal('net', 12, 2)->default(0);
                $table->string('deduction_note')->nullable();
                $table->string('status', 20)->default('draft');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['driver_id', 'month']);
            });
        }

        if (! Schema::hasTable('driver_payroll_items')) {
            Schema::create('driver_payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_payroll_id')->constrained('driver_payrolls')->cascadeOnDelete();
                $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests')->nullOnDelete();
                $table->unsignedInteger('scheduled_days')->default(0);
                $table->unsignedInteger('worked_days')->default(0);
                $table->unsignedInteger('leave_days')->default(0);
                $table->unsignedInteger('absent_days')->default(0);
                $table->unsignedInteger('holiday_days')->default(0);
                $table->unsignedInteger('parent_skip_days')->default(0);
                $table->decimal('daily_rate', 12, 2)->default(0);
                $table->decimal('gross', 12, 2)->default(0);
                $table->decimal('deduction', 12, 2)->default(0);
                $table->decimal('net', 12, 2)->default(0);
                $table->string('deduction_note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shift_replacements')) {
            Schema::create('shift_replacements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
                $table->date('date');
                $table->foreignId('original_driver_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('replacement_driver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('original_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->foreignId('replacement_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('reason', 30);
                $table->string('status', 20)->default('open');
                $table->text('notes')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index(['pickup_request_id', 'date']);
                $table->index(['status', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_replacements');
        Schema::dropIfExists('driver_payroll_items');
        Schema::dropIfExists('driver_payrolls');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'pickup_request_id')) {
                $table->dropConstrainedForeignId('pickup_request_id');
            }
        });

        Schema::table('shift_attendances', function (Blueprint $table) {
            foreach (['refunded_amount', 'refunded_at', 'refund_reason'] as $col) {
                if (Schema::hasColumn('shift_attendances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('pickup_request_stops', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_request_stops', 'leg')) {
                $table->dropColumn('leg');
            }
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'round_trip')) {
                $table->dropColumn('round_trip');
            }
        });
    }
};
