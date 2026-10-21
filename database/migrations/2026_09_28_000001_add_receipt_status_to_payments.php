<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'receipt_status')) {
                $table->string('receipt_status', 20)->default('pending')->after('status');
            }
        });

        DB::table('payments')
            ->where('status', 'completed')
            ->update(['receipt_status' => 'received']);

        DB::table('payments')
            ->where('status', 'pending')
            ->whereNotNull('proof_path')
            ->update(['receipt_status' => 'not_received']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'receipt_status')) {
                $table->dropColumn('receipt_status');
            }
        });
    }
};
