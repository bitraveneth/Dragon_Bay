<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $selectedType = $request->query('type');
        $validTypes = ['asset', 'liability', 'equity', 'income', 'expense'];

        if (! in_array($selectedType, $validTypes, true)) {
            $selectedType = null;
        }

        $allAccounts = Account::orderBy('code')->get();

        $accounts = Account::query()
            ->when($selectedType, fn ($query) => $query->where('type', $selectedType))
            ->orderBy('code')
            ->get();

        return view('admin.finance.accounts', compact('accounts', 'allAccounts', 'selectedType'));
    }

    public function create()
    {
        return view('admin.finance.accounts_edit', ['account' => new Account()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Account::create($data);

        return redirect()->route('admin.accounts.index')->with('status', 'Account created.');
    }

    public function edit(Account $account)
    {
        return view('admin.finance.accounts_edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validated($request);

        if ($data['name'] !== $account->name && LedgerEntry::where('account', $account->name)->exists()) {
            return redirect()
                ->route('admin.accounts.edit', $account)
                ->withErrors(['name' => 'Accounts referenced by ledger entries cannot be renamed.']);
        }

        $account->update($data);

        return redirect()->route('admin.accounts.index')->with('status', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        if (LedgerEntry::where('account', $account->name)->exists()) {
            return redirect()
                ->route('admin.accounts.index')
                ->with('error', 'Account is referenced by ledger entries and cannot be deleted.');
        }

        $account->delete();

        return redirect()->route('admin.accounts.index')->with('status', 'Account deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts', 'code')->ignore($request->route('account')?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')->ignore($request->route('account')?->id),
            ],
            'type' => 'required|in:asset,liability,equity,income,expense',
            'is_active' => 'sometimes|boolean',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
