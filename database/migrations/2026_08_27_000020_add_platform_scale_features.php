<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('sms_enabled')->default(false);
                $table->string('sms_provider')->nullable();
                $table->string('sms_api_url')->nullable();
                $table->text('sms_api_key')->nullable();
                $table->string('sms_sender')->nullable();
                $table->boolean('fcm_enabled')->default(false);
                $table->text('fcm_server_key')->nullable();
                $table->boolean('jazzcash_enabled')->default(false);
                $table->string('jazzcash_merchant_id')->nullable();
                $table->string('jazzcash_password')->nullable();
                $table->text('jazzcash_integrity_salt')->nullable();
                $table->string('jazzcash_return_url')->nullable();
                $table->boolean('easypaisa_enabled')->default(false);
                $table->string('easypaisa_store_id')->nullable();
                $table->text('easypaisa_hash_key')->nullable();
                $table->string('easypaisa_return_url')->nullable();
                $table->unsignedInteger('cancel_hours')->default(24);
                $table->decimal('cancel_fee_percent', 5, 2)->default(0);
                $table->unsignedInteger('geofence_meters')->default(300);
                $table->decimal('referral_bonus', 12, 2)->default(0);
                $table->boolean('pickup_otp_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('token', 512);
                $table->string('platform')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'token']);
            });
        }

        if (! Schema::hasTable('driver_location_logs')) {
            Schema::create('driver_location_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->timestamp('recorded_at');
                $table->index(['user_id', 'recorded_at']);
            });
        }

        if (! Schema::hasTable('shift_day_runs')) {
            Schema::create('shift_day_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
                $table->date('date');
                $table->string('status')->default('scheduled');
                $table->string('pickup_otp', 6)->nullable();
                $table->string('pickup_photo_path')->nullable();
                $table->timestamp('pickup_verified_at')->nullable();
                $table->timestamp('arrival_notified_at')->nullable();
                $table->timestamps();
                $table->unique(['pickup_request_id', 'date']);
            });
        }

        if (! Schema::hasTable('shift_day_stop_logs')) {
            Schema::create('shift_day_stop_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shift_day_run_id')->constrained('shift_day_runs')->cascadeOnDelete();
                $table->foreignId('pickup_request_stop_id')->constrained('pickup_request_stops')->cascadeOnDelete();
                $table->string('status')->default('pending');
                $table->string('photo_path')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['shift_day_run_id', 'pickup_request_stop_id'], 'day_run_stop_unique');
            });
        } else {
            $indexNames = collect(Schema::getIndexes('shift_day_stop_logs'))->pluck('name')->all();
            if (! in_array('day_run_stop_unique', $indexNames, true)) {
                Schema::table('shift_day_stop_logs', function (Blueprint $table) {
                    $table->unique(['shift_day_run_id', 'pickup_request_stop_id'], 'day_run_stop_unique');
                });
            }
        }

        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('type');
                $table->string('reason')->nullable();
                $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (! Schema::hasColumn('users', 'referred_by')) {
                $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'referral_balance')) {
                $table->decimal('referral_balance', 12, 2)->default(0)->after('referred_by');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('school_name')->constrained('schools')->nullOnDelete();
            }
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('pickup_requests', 'passenger_count')) {
                $table->unsignedInteger('passenger_count')->default(1)->after('student_id');
            }
            if (! Schema::hasColumn('pickup_requests', 'cancellation_fee')) {
                $table->decimal('cancellation_fee', 12, 2)->nullable()->after('estimated_amount');
            }
        });

        Schema::table('driver_vehicle_verifications', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_vehicle_verifications', 'insurance_expiry')) {
                $table->date('insurance_expiry')->nullable()->after('license_plate');
            }
            if (! Schema::hasColumn('driver_vehicle_verifications', 'registration_expiry')) {
                $table->date('registration_expiry')->nullable()->after('insurance_expiry');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'gateway_txn_ref')) {
                $table->string('gateway_txn_ref')->nullable()->after('stripe_payment_intent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('gateway_txn_ref');
        });
        Schema::table('driver_vehicle_verifications', function (Blueprint $table) {
            $table->dropColumn(['insurance_expiry', 'registration_expiry']);
        });
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['passenger_count', 'cancellation_fee']);
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn(['emergency_contact_name', 'emergency_contact_phone', 'referral_balance']);
        });
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('shift_day_stop_logs');
        Schema::dropIfExists('shift_day_runs');
        Schema::dropIfExists('driver_location_logs');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('platform_settings');
    }
};
