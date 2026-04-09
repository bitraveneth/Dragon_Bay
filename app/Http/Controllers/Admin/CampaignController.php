<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    private const VALID_STATUSES = [
        Campaign::STATUS_PLANNED,
        Campaign::STATUS_RUNNING,
        Campaign::STATUS_COMPLETED,
    ];

    public function index()
    {
        $campaigns = Campaign::orderByDesc('start_date')->paginate(15);

        return view('admin.marketing.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.marketing.campaigns.create', [
            'campaign' => new Campaign(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('marketing/campaigns', 'public');
        }

        if ($this->payloadPostsLedgerEntries($data)) {
            $this->ensureCanManageLedgerEntries();
        }

        DB::transaction(function () use ($data) {
            $campaign = Campaign::create($data);
            $this->syncLedgerEntries($campaign);
        });

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign saved.');
    }

    public function edit(Campaign $campaign)
    {
        return view('admin.marketing.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            if ($campaign->attachment_path) {
                Storage::disk('public')->delete($campaign->attachment_path);
            }

            $data['attachment_path'] = $request->file('attachment')->store('marketing/campaigns', 'public');
        }

        if ($this->hasPostedLedgerEntries($campaign) || $this->payloadPostsLedgerEntries($data)) {
            $this->ensureCanManageLedgerEntries();
        }

        DB::transaction(function () use ($campaign, $data) {
            $campaign->update($data);
            $this->syncLedgerEntries($campaign);
        });

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($this->hasPostedLedgerEntries($campaign)) {
            $this->ensureCanManageLedgerEntries();
        }

        if ($campaign->attachment_path) {
            Storage::disk('public')->delete($campaign->attachment_path);
        }

        DB::transaction(function () use ($campaign) {
            $this->deleteLedgerEntries($campaign);
            $campaign->delete();
        });

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reach' => 'nullable|integer|min:0',
            'impressions' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'campaign_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);
    }

    protected function syncLedgerEntries(Campaign $campaign): void
    {
        $this->deleteLedgerEntries($campaign);

        if ((float) $campaign->cost <= 0 || ! in_array($campaign->status, Campaign::ACTUAL_COST_STATUSES, true)) {
            return;
        }

        $description = $this->ledgerDescription($campaign);

        LedgerEntry::create([
            'account' => 'Marketing Expense',
            'description' => $description,
            'debit' => $campaign->cost,
            'credit' => 0,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => $description,
            'debit' => 0,
            'credit' => $campaign->cost,
        ]);
    }

    protected function deleteLedgerEntries(Campaign $campaign): void
    {
        LedgerEntry::where('description', $this->ledgerDescription($campaign))->delete();
    }

    protected function ledgerDescription(Campaign $campaign): string
    {
        return 'Campaign expense #' . $campaign->id;
    }

    protected function payloadPostsLedgerEntries(array $data): bool
    {
        return (float) ($data['cost'] ?? 0) > 0
            && in_array(Campaign::normalizeStatus($data['status'] ?? null), Campaign::ACTUAL_COST_STATUSES, true);
    }

    protected function hasPostedLedgerEntries(Campaign $campaign): bool
    {
        return LedgerEntry::where('description', $this->ledgerDescription($campaign))->exists();
    }

    protected function ensureCanManageLedgerEntries(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'accounting.manage')) {
            abort(403, 'Accounting permission is required to post or remove campaign ledger entries.');
        }
    }
}
