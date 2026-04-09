<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'number',
        'issued_at',
        'due_at',
        'net_total',
        'vat_amount',
        'withholding',
        'status',
    ];

    protected $casts = [
        'issued_at'   => 'date',
        'due_at'      => 'date',
        'net_total'   => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'withholding' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function advanceApplications()
    {
        return $this->hasMany(AgentAdvanceApplication::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function getGrossTotalAttribute(): float
    {
        return (float) ($this->net_total + $this->vat_amount);
    }

    public function getCashTotalAttribute(): float
    {
        return (float) ($this->gross_total - $this->withholding);
    }

    public function getCreditsTotalAttribute(): float
    {
        if ($this->relationLoaded('creditNotes')) {
            return (float) $this->creditNotes->sum('amount');
        }

        return (float) $this->creditNotes()->sum('amount');
    }

    public function getReceiptsTotalAttribute(): float
    {
        if ($this->relationLoaded('receipts')) {
            return (float) $this->receipts->sum('amount');
        }

        return (float) $this->receipts()->sum('amount');
    }

    public function getAdvancesAppliedTotalAttribute(): float
    {
        if ($this->relationLoaded('advanceApplications')) {
            return (float) $this->advanceApplications->sum('amount');
        }

        return (float) $this->advanceApplications()->sum('amount');
    }

    public function creditBreakdown(float $grossAmount): array
    {
        $grossAmount = round(max($grossAmount, 0.0), 2);
        $netTotal = (float) $this->net_total;
        $vatAmount = (float) $this->vat_amount;

        if ($grossAmount <= 0.0 || $netTotal <= 0.0 || $vatAmount <= 0.0) {
            return [
                'net' => $grossAmount,
                'vat' => 0.0,
            ];
        }

        $rate = $vatAmount / $netTotal;
        $net = round($grossAmount / (1 + $rate), 2);
        $vat = round($grossAmount - $net, 2);

        return [
            'net' => max($net, 0.0),
            'vat' => max($vat, 0.0),
        ];
    }

    public function getCreditNotesNetTotalAttribute(): float
    {
        $creditNotes = $this->relationLoaded('creditNotes')
            ? $this->creditNotes
            : $this->creditNotes()->get();

        return (float) $creditNotes->sum(function (CreditNote $creditNote) {
            return $this->creditBreakdown((float) $creditNote->amount)['net'];
        });
    }

    public function getCreditNotesVatTotalAttribute(): float
    {
        $creditNotes = $this->relationLoaded('creditNotes')
            ? $this->creditNotes
            : $this->creditNotes()->get();

        return (float) $creditNotes->sum(function (CreditNote $creditNote) {
            return $this->creditBreakdown((float) $creditNote->amount)['vat'];
        });
    }

    public function getNetSalesAfterCreditsAttribute(): float
    {
        return max(0.0, (float) $this->net_total - $this->credit_notes_net_total);
    }

    public function creditNotesInRange(Carbon|string $from, Carbon|string $to): Collection
    {
        $fromDate = $this->normalizeDateBoundary($from)->startOfDay();
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('creditNotes')) {
            return $this->creditNotes
                ->filter(function (CreditNote $creditNote) use ($fromDate, $toDate) {
                    if (! $creditNote->issued_at) {
                        return false;
                    }

                    return $creditNote->issued_at->copy()->startOfDay()->between($fromDate, $toDate, true);
                })
                ->values();
        }

        return $this->creditNotes()
            ->whereDate('issued_at', '>=', $fromDate->toDateString())
            ->whereDate('issued_at', '<=', $toDate->toDateString())
            ->get();
    }

    public function creditNotesTotalInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) $this->creditNotesInRange($from, $to)->sum('amount');
    }

    public function creditNotesTotalThrough(Carbon|string $to): float
    {
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('creditNotes')) {
            return (float) $this->creditNotes
                ->filter(function (CreditNote $creditNote) use ($toDate) {
                    if (! $creditNote->issued_at) {
                        return false;
                    }

                    return $creditNote->issued_at->copy()->startOfDay()->lte($toDate);
                })
                ->sum('amount');
        }

        return (float) $this->creditNotes()
            ->whereDate('issued_at', '<=', $toDate->toDateString())
            ->sum('amount');
    }

    public function creditNotesNetTotalInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) $this->creditNotesInRange($from, $to)->sum(function (CreditNote $creditNote) {
            return $this->creditBreakdown((float) $creditNote->amount)['net'];
        });
    }

    public function creditNotesVatTotalInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) $this->creditNotesInRange($from, $to)->sum(function (CreditNote $creditNote) {
            return $this->creditBreakdown((float) $creditNote->amount)['vat'];
        });
    }

    public function netSalesAfterCreditsInRange(Carbon|string $from, Carbon|string $to): float
    {
        return max(0.0, (float) $this->net_total - $this->creditNotesNetTotalInRange($from, $to));
    }

    public function receiptsInRange(Carbon|string $from, Carbon|string $to): Collection
    {
        $fromDate = $this->normalizeDateBoundary($from)->startOfDay();
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('receipts')) {
            return $this->receipts
                ->filter(function (Receipt $receipt) use ($fromDate, $toDate) {
                    if (! $receipt->received_at) {
                        return false;
                    }

                    return $receipt->received_at->copy()->startOfDay()->between($fromDate, $toDate, true);
                })
                ->values();
        }

        return $this->receipts()
            ->whereDate('received_at', '>=', $fromDate->toDateString())
            ->whereDate('received_at', '<=', $toDate->toDateString())
            ->get();
    }

    public function receiptsTotalInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) $this->receiptsInRange($from, $to)->sum('amount');
    }

    public function receiptsTotalThrough(Carbon|string $to): float
    {
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('receipts')) {
            return (float) $this->receipts
                ->filter(function (Receipt $receipt) use ($toDate) {
                    if (! $receipt->received_at) {
                        return false;
                    }

                    return $receipt->received_at->copy()->startOfDay()->lte($toDate);
                })
                ->sum('amount');
        }

        return (float) $this->receipts()
            ->whereDate('received_at', '<=', $toDate->toDateString())
            ->sum('amount');
    }

    public function advanceApplicationsInRange(Carbon|string $from, Carbon|string $to): Collection
    {
        $fromDate = $this->normalizeDateBoundary($from)->startOfDay();
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('advanceApplications')) {
            return $this->advanceApplications
                ->filter(function (AgentAdvanceApplication $application) use ($fromDate, $toDate) {
                    if (! $application->applied_at) {
                        return false;
                    }

                    return $application->applied_at->copy()->startOfDay()->between($fromDate, $toDate, true);
                })
                ->values();
        }

        return $this->advanceApplications()
            ->whereDate('applied_at', '>=', $fromDate->toDateString())
            ->whereDate('applied_at', '<=', $toDate->toDateString())
            ->get();
    }

    public function advancesAppliedTotalInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) $this->advanceApplicationsInRange($from, $to)->sum('amount');
    }

    public function advancesAppliedTotalThrough(Carbon|string $to): float
    {
        $toDate = $this->normalizeDateBoundary($to)->endOfDay();

        if ($this->relationLoaded('advanceApplications')) {
            return (float) $this->advanceApplications
                ->filter(function (AgentAdvanceApplication $application) use ($toDate) {
                    if (! $application->applied_at) {
                        return false;
                    }

                    return $application->applied_at->copy()->startOfDay()->lte($toDate);
                })
                ->sum('amount');
        }

        return (float) $this->advanceApplications()
            ->whereDate('applied_at', '<=', $toDate->toDateString())
            ->sum('amount');
    }

    public function outstandingAsOf(Carbon|string $to): float
    {
        return max(0.0, (float) (
            $this->cash_total
            - $this->creditNotesTotalThrough($to)
            - $this->receiptsTotalThrough($to)
            - $this->advancesAppliedTotalThrough($to)
        ));
    }

    public function getOutstandingAttribute(): float
    {
        return max(0.0, (float) ($this->cash_total - $this->credits_total - $this->receipts_total - $this->advances_applied_total));
    }

    /**
     * Recalculate the invoice status based on payments / credits applied.
     *
     * Status rules:
     * - paid      : outstanding <= 0 and some movement
     * - adjusted  : some payments or credits applied but still outstanding
     * - issued    : no payments or credits applied yet
     */
    public function recalculateStatus(): void
    {
        $outstanding = $this->outstanding;
        $hasMovement = ($this->credits_total > 0.0)
            || ($this->receipts_total > 0.0)
            || ($this->advances_applied_total > 0.0);

        if ($outstanding <= 0.00001 && $hasMovement) {
            $this->status = 'paid';
        } elseif ($hasMovement) {
            $this->status = 'adjusted';
        } else {
            $this->status = 'issued';
        }

        $this->save();
    }

    protected function normalizeDateBoundary(Carbon|string $value): Carbon
    {
        return $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse($value);
    }
}
