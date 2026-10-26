<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Invoices that were partly paid before the "partial" status existed were left as "unpaid".
 * Mark them partial (some money received, but less than the total). Overdue invoices keep "overdue".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('invoices')
            ->where('status', 'unpaid')
            ->where('amount_paid', '>', 0)
            ->whereColumn('amount_paid', '<', 'total')
            ->update(['status' => 'partial']);
    }

    public function down(): void
    {
        DB::table('invoices')->where('status', 'partial')->update(['status' => 'unpaid']);
    }
};
