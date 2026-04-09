<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    private const VALID_STATUSES = [
        Expense::STATUS_RECORDED,
        Expense::STATUS_REVIEWED,
    ];

    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = Expense::whereBetween('date', [$from, $to]);

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $expenses = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $total = (clone $query)->sum('amount');

        $categories = Expense::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.finance.expenses.index', compact('expenses', 'from', 'to', 'total', 'categories'));
    }

    public function create()
    {
        return view('admin.finance.expenses.create', [
            'expense' => new Expense(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $expense = Expense::create($data);
            $this->syncLedgerEntries($expense);
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.finance.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            $this->syncLedgerEntries($expense);
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($this->hasPostedLedgerEntries($expense)) {
            return redirect()
                ->route('admin.expenses.index')
                ->withErrors([
                    'expense' => 'Posted expenses cannot be deleted. Preserve the audit trail and record a correcting adjustment instead.',
                ]);
        }

        DB::transaction(function () use ($expense) {
            $this->deleteLedgerEntries($expense);
            $expense->delete();
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
        ]);
    }

    protected function syncLedgerEntries(Expense $expense): void
    {
        $this->deleteLedgerEntries($expense);

        if ((float) $expense->amount <= 0 || ! in_array($expense->status, self::VALID_STATUSES, true)) {
            return;
        }

        $description = $this->ledgerDescription($expense);
        $account = $this->expenseAccount($expense);

        LedgerEntry::create([
            'account' => $account,
            'description' => $description,
            'debit' => $expense->amount,
            'credit' => 0,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => $description,
            'debit' => 0,
            'credit' => $expense->amount,
        ]);
    }

    protected function deleteLedgerEntries(Expense $expense): void
    {
        LedgerEntry::where('description', $this->ledgerDescription($expense))->delete();
    }

    protected function hasPostedLedgerEntries(Expense $expense): bool
    {
        return LedgerEntry::where('description', $this->ledgerDescription($expense))->exists();
    }

    protected function ledgerDescription(Expense $expense): string
    {
        return 'Expense #' . $expense->id;
    }

    protected function expenseAccount(Expense $expense): string
    {
        $category = strtolower(trim((string) $expense->category));

        if (str_contains($category, 'marketing')) {
            return 'Marketing Expense';
        }

        if (str_contains($category, 'salary') || str_contains($category, 'payroll')) {
            return 'Payroll Expense';
        }

        if (str_contains($category, 'utility')) {
            return 'Utilities Expense';
        }

        return 'Selling & Distribution Expense';
    }
}
