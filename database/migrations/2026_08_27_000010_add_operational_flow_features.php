<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('last_lat', 10, 7)->nullable()->after('service_areas');
            $table->decimal('last_lng', 10, 7)->nullable()->after('last_lat');
            $table->timestamp('last_location_at')->nullable()->after('last_lng');
            $table->string('last_ride_status')->nullable()->after('last_location_at');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('phone_otp', 6)->nullable()->after('otp');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp');
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->timestamp('match_expires_at')->nullable()->after('status');
            $table->unsignedInteger('auto_assign_attempts')->default(0)->after('match_expires_at');
            $table->string('assignment_source')->nullable()->after('auto_assign_attempts');
            $table->boolean('auto_renew')->default(false)->after('assignment_source');
            $table->string('renewal_status')->default('none')->after('auto_renew');
            $table->foreignId('renewed_from_id')->nullable()->after('renewal_status')->constrained('pickup_requests')->nullOnDelete();
            $table->date('last_delay_notified_on')->nullable()->after('renewed_from_id');
            $table->timestamp('renewal_notified_at')->nullable()->after('last_delay_notified_on');
        });

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->string('type')->default('general')->after('pickup_request_id');
            $table->unsignedInteger('eta_minutes')->nullable()->after('type');
            $table->string('reporter_role')->nullable()->after('eta_minutes');
            $table->text('admin_notes')->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->after('admin_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('paid_at');
            $table->string('reject_reason')->nullable()->after('rejected_at');
            $table->timestamp('refunded_at')->nullable()->after('reject_reason');
            $table->string('refund_reason')->nullable()->after('refunded_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('sent_at');
            $table->string('kind')->default('shift')->after('notes');
        });

        Schema::create('shift_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
            $table->date('date');
            $table->string('status'); // present | skipped | holiday | absent
            $table->string('reason')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pickup_request_id', 'date']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('type')->default('public'); // public | school | custom
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'city_id']);
        });

        Schema::create('sos_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests')->nullOnDelete();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('message')->nullable();
            $table->string('status')->default('open'); // open | acknowledged | resolved
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained('pickup_requests')->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->unique(['pickup_request_id', 'from_user_id', 'to_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('sos_alerts');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('shift_attendances');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'kind']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'reject_reason', 'refunded_at', 'refund_reason']);
        });

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn(['type', 'eta_minutes', 'reporter_role', 'admin_notes', 'resolved_at']);
        });

        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('renewed_from_id');
            $table->dropColumn([
                'match_expires_at',
                'auto_assign_attempts',
                'assignment_source',
                'auto_renew',
                'renewal_status',
                'last_delay_notified_on',
                'renewal_notified_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_lat',
                'last_lng',
                'last_location_at',
                'last_ride_status',
                'phone_verified_at',
                'phone_otp',
                'phone_otp_expires_at',
            ]);
        });
    }
};
