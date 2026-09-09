<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pickup_requests')) {
            return;
        }

        Schema::table('pickup_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('pickup_requests', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('pickup_requests', 'cancelled_by_role')) {
                $table->string('cancelled_by_role', 30)->nullable()->after('cancelled_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pickup_requests')) {
            return;
        }

        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'cancelled_by')) {
                $table->dropConstrainedForeignId('cancelled_by');
            }
            if (Schema::hasColumn('pickup_requests', 'cancelled_by_role')) {
                $table->dropColumn('cancelled_by_role');
            }
        });
    }
};
