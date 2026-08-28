<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'student_id',
        'pickup_request_id',
        'issued_by',
        'status',
        'currency',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'total',
        'amount_paid',
        'issue_date',
        'due_date',
        'sent_at',
        'paid_at',
        'cancelled_at',
        'payment_method',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'notes',
        'terms',
        'kind',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'tax_percent' => 'float',
            'tax_amount' => 'float',
            'total' => 'float',
            'amount_paid' => 'float',
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPayable(): bool
    {
        return in_array($this->status, [self::STATUS_UNPAID, self::STATUS_OVERDUE, self::STATUS_DRAFT], true)
            && $this->balance() > 0;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function hasPendingBankTransfer(): bool
    {
        $this->loadMissing('payments');

        return $this->payments->contains(
            fn (Payment $payment) => $payment->method === Payment::METHOD_BANK
                && $payment->status === Payment::STATUS_PENDING
        );
    }

    public function balance(): float
    {
        return max(0, round($this->total - $this->amount_paid, 2));
    }

    public function formattedTotal(): string
    {
        return $this->formatMoney($this->total);
    }

    public function formatMoney(float $amount): string
    {
        return strtoupper($this->currency) . ' ' . number_format($amount, 2);
    }

    public static function nextNumber(?string $prefix = null): string
    {
        $prefix = strtoupper(trim((string) ($prefix ?: 'INV')));
        $year = now()->year;

        return DB::transaction(function () use ($prefix, $year) {
            $last = static::query()
                ->where('invoice_number', 'like', $prefix . '-' . $year . '-%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('invoice_number');

            $seq = 1;
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $seq = ((int) $m[1]) + 1;
            }

            return sprintf('%s-%d-%04d', $prefix, $year, $seq);
        });
    }

    public function syncOverdueStatus(): void
    {
        if (
            in_array($this->status, [self::STATUS_UNPAID, self::STATUS_OVERDUE], true)
            && $this->due_date
            && $this->due_date->lt(now()->startOfDay())
            && $this->balance() > 0
        ) {
            $this->status = self::STATUS_OVERDUE;
            $this->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['items', 'student', 'customer', 'payments']);
        $settings = PaymentSetting::current();

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'pickup_request_id' => $this->pickup_request_id,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax_percent' => $this->tax_percent,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'amount_paid' => $this->amount_paid,
            'balance' => $this->balance(),
            'formatted_total' => $this->formattedTotal(),
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'student' => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->name,
            ] : null,
            'items' => $this->items->map(fn (InvoiceItem $item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total,
            ])->values()->all(),
            'payable' => $this->isPayable(),
            'stripe_enabled' => PaymentSetting::current()->hasStripe(),
            'bank' => $this->isPayable() ? $settings->bankDetails() : null,
            'payments' => $this->payments->map(fn (Payment $p) => $p->toApiArray())->values()->all(),
        ];
    }
}
