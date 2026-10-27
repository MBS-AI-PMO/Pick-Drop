<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Invoices whose payments were all refunded before the "refunded" invoice status existed were left as
 * unpaid/overdue. Mark them refunded (only when nothing is still paid and a refund really exists).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('invoices')
            ->whereIn('status', ['unpaid', 'overdue'])
            ->where('amount_paid', '<=', 0)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.invoice_id', 'invoices.id')
                    ->where('payments.status', 'refunded');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.invoice_id', 'invoices.id')
                    ->where('payments.status', 'completed');
            })
            ->update(['status' => 'refunded', 'paid_at' => null]);
    }

    public function down(): void
    {
        // Not reversible: the previous unpaid/overdue value is recalculated from due dates anyway.
    }
};
