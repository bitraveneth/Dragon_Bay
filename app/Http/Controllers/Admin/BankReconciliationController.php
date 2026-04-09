<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $range] = $this->resolvePeriod($request);

        $receipts = Receipt::with('invoice')
            ->whereBetween('received_at', [$from, $to])
            ->orderBy('received_at')
            ->get();

        $rangeOptions = [
            '7d' => 'Last 7 days',
            '1m' => 'Last 1 month',
            '3m' => 'Last 3 months',
            '1y' => 'Last 1 year',
            'all' => 'All time',
            'custom' => 'Custom range',
        ];

        return view('admin.finance.reconciliation', compact('receipts', 'from', 'to', 'range', 'rangeOptions'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'range' => 'nullable|string|in:7d,1m,3m,1y,all,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'reconciled' => 'array',
            'reconciled.*' => 'integer|exists:receipts,id',
        ]);

        [$from, $to, $range] = $this->resolvePeriod($request);

        $visibleIds = Receipt::query()
            ->whereBetween('received_at', [$from, $to])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $reconciledIds = collect($data['reconciled'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($visibleIds)
            ->values();

        if ($visibleIds->isNotEmpty()) {
            Receipt::whereIn('id', $visibleIds)->update(['reconciled' => false]);
            Receipt::whereIn('id', $reconciledIds)->update(['reconciled' => true]);
        }

        return redirect()
            ->route('admin.finance.reconciliation', [
                'range' => $range,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ])
            ->with('status', 'Receipt reconciliation updated.');
    }

    protected function resolvePeriod(Request $request): array
    {
        $range = $request->input('range');
        $hasCustomDates = $request->filled('from') || $request->filled('to');

        if ($range === 'custom' || (! $range && $hasCustomDates)) {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'))->startOfDay()
                : Carbon::now()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'))->endOfDay()
                : Carbon::now()->endOfMonth();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, 'custom'];
        }

        $now = Carbon::now();

        switch ($range) {
            case '7d':
                $from = $now->copy()->subDays(6)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '1m':
                $from = $now->copy()->subMonth()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '3m':
                $from = $now->copy()->subMonths(3)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '1y':
                $from = $now->copy()->subYear()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'all':
                $oldestReceiptDate = Receipt::query()->min('received_at');
                $newestReceiptDate = Receipt::query()->max('received_at');

                $from = $oldestReceiptDate
                    ? Carbon::parse($oldestReceiptDate)->startOfDay()
                    : $now->copy()->startOfMonth();
                $to = $newestReceiptDate
                    ? Carbon::parse($newestReceiptDate)->endOfDay()
                    : $now->copy()->endOfMonth();
                break;
            default:
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $range = 'custom';
                break;
        }

        return [$from, $to, $range];
    }
}
