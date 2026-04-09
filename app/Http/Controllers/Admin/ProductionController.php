<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\ProductionMaterialIssue;
use App\Models\ProductionMaterialIssueItem;
use App\Models\ProductionRun;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $runs = ProductionRun::with('product', 'batch', 'warehouse', 'supervisor', 'approver')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest()
            ->paginate(10);

        $today = now()->toDateString();
        $todayRuns = ProductionRun::whereDate('created_at', $today)->get();

        $byLineShift = $todayRuns
            ->groupBy(fn ($run) => ($run->line ?: 'Unassigned') . '|' . ($run->shift ?: 'All'))
            ->map(function ($group) {
                return $group->sum('quantity');
            });

        // Capacity map keyed in a normalised "line|shift" (lowercase) form so that
        // free-text input like "Line 1" / "morning" still matches.
        $lineCapacities = [
            'line 1|morning' => 50000,
            'line 1|evening' => 50000,
            'line 2|morning' => 40000,
            'line 2|evening' => 40000,
        ];

        return view('admin.production.index', compact('runs', 'byLineShift', 'lineCapacities', 'today'));
    }

    /**
     * List QC-approved production runs that have not yet been confirmed to stock.
     * This is mainly for the warehouse manager to process goods receipts.
     */
    public function pendingReceipts()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $runs = ProductionRun::with(['product', 'batch', 'warehouse', 'approver'])
            ->where('qc_status', 'approved')
            ->whereNull('stock_confirmed_at')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest()
            ->get();

        return view('admin.production.pending_receipts', compact('runs'));
    }

    public function create()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        // Only finished products should be selectable for production runs
        $products = Product::where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Pre-compute estimated material unit cost per finished unit for each product
        $productUnitCosts = [];
        $productIds = $products->pluck('id')->all();

        if (!empty($productIds)) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');

            foreach ($products as $product) {
                $bom = $boms->get($product->id);
                if (! $bom || $bom->items->isEmpty()) {
                    continue;
                }

                $unitCost = null;

                if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
                    // Explicit override on BOM
                    $unitCost = (float) $bom->material_unit_cost;
                } else {
                    // Derive from components
                    $accumulator = 0.0;
                    foreach ($bom->items as $item) {
                        $component = $item->component;
                        $baseCost = null;
                        if ($item->unit_cost !== null) {
                            $baseCost = (float) $item->unit_cost;
                        } elseif ($component && $component->standard_cost !== null) {
                            $baseCost = (float) $component->standard_cost;
                        }
                        if ($baseCost !== null) {
                            $accumulator += $baseCost * (float) $item->quantity;
                        }
                    }
                    if ($accumulator > 0) {
                        $unitCost = $accumulator;
                    }
                }

                if ($unitCost !== null) {
                    $productUnitCosts[$product->id] = $unitCost;
                }
            }
        }
        $batches = Batch::orderBy('production_date', 'desc')->get();
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();

        // Choose a sensible default warehouse for production. We prefer any
        // warehouse marked as "factory"; if none exists, we leave it null and
        // let the select fall back to the placeholder.
        $defaultWarehouseId = optional(
            $warehouses->firstWhere('type', 'factory')
        )->id;

        $employees = Employee::orderBy('name')->get();

        // Pre-compute required materials per product and warehouse stock
        $materialRequirements = [];
        $warehouseStock = [];

        $productIds = $products->pluck('id')->all();
        if (! empty($productIds)) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');

            foreach ($products as $product) {
                $bom = $boms->get($product->id);
                if (! $bom || $bom->items->isEmpty()) {
                    continue;
                }

                $components = [];
                foreach ($bom->items as $item) {
                    if (! $item->component_product_id || ! $item->quantity) {
                        continue;
                    }
                    $components[] = [
                        'product_id' => $item->component_product_id,
                        'name'       => $item->component?->name,
                        'sku'        => $item->component?->sku,
                        'uom'        => $item->component?->uom,
                        'type'       => $item->component?->product_type,
                        'quantity_per_unit' => (float) $item->quantity,
                    ];
                }

                if (! empty($components)) {
                    $materialRequirements[$product->id] = $components;
                }
            }

            // Simple stock snapshot per warehouse+product
            $stockEntries = StockEntry::selectRaw('warehouse_id, product_id, SUM(quantity) as qty')
                ->where('status', 'available')
                ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                    $query->whereIn('warehouse_id', $warehouseIds);
                })
                ->groupBy('warehouse_id', 'product_id')
                ->get();

            foreach ($stockEntries as $entry) {
                $warehouseStock[$entry->warehouse_id][$entry->product_id] = (float) $entry->qty;
            }
        }

        return view('admin.production.create', [
            'products'           => $products,
            'batches'            => $batches,
            'warehouses'         => $warehouses,
            'employees'          => $employees,
            'defaultWarehouseId' => $defaultWarehouseId,
            'productUnitCosts'   => $productUnitCosts,
            'materialRequirements' => $materialRequirements,
            'warehouseStock'       => $warehouseStock,
        ]);
    }

    public function edit(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $production->load('product', 'batch', 'warehouse');
        return view('admin.production.edit', ['run' => $production]);
    }

    public function show(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $production->load('product', 'batch', 'warehouse', 'supervisor', 'approver', 'stockConfirmer', 'materialIssues.items.component', 'materialIssues.issuer');

        // Load the active BOM for this product (if any) so the view can show
        // a simple summary of components required for this run and an
        // estimated material cost based on component standard_cost.
        $bom = BillOfMaterial::where('product_id', $production->product_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->with(['items.component'])
            ->first();

        $estimatedUnitCost = null;
        $estimatedTotalCost = null;

        if ($bom && $bom->items->isNotEmpty()) {
            $unitCostAccumulator = 0.0;

            foreach ($bom->items as $item) {
                $component = $item->component;
                $componentUnitCost = 0.0;

                if ($component && $component->standard_cost !== null) {
                    // Standard cost is per 1 unit of the component; multiply by
                    // quantity required for a single finished unit.
                    $componentUnitCost = (float) $component->standard_cost * (float) $item->quantity;
                }

                // Attach helper attributes so the Blade view can display a
                // per-component cost breakdown without re-doing the maths.
                $item->calculated_unit_cost = $componentUnitCost;
                $item->calculated_total_cost = $componentUnitCost * (float) $production->quantity;

                $unitCostAccumulator += $componentUnitCost;
            }

            $estimatedUnitCost = $unitCostAccumulator;
            $estimatedTotalCost = $estimatedUnitCost * (float) $production->quantity;
        }

        // If we already have a persisted costing snapshot, prefer that for the
        // header summary while still using the BOM-derived breakdown table.
        if (! is_null($production->material_unit_cost)) {
            $estimatedUnitCost = (float) $production->material_unit_cost;
        }
        if (! is_null($production->material_total_cost)) {
            $estimatedTotalCost = (float) $production->material_total_cost;
        }

        // If stock has been confirmed we can try to locate the finished-goods
        // stock entry so that the UI can offer a quick write-off shortcut.
        $stockEntry = null;
        if ($production->stock_confirmed_at && $production->warehouse_id) {
            $stockEntry = StockEntry::where('warehouse_id', $production->warehouse_id)
                ->where('product_id', $production->product_id)
                ->where('batch_id', $production->batch_id)
                ->orderByDesc('updated_at')
                ->first();
        }

        return view('admin.production.show', [
            'run'                => $production,
            'bom'                => $bom,
            'stockEntry'         => $stockEntry,
            'estimatedUnitCost'  => $estimatedUnitCost,
            'estimatedTotalCost' => $estimatedTotalCost,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'supervisor_id' => 'nullable|exists:employees,id',
            'materials_reserved' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess($data['warehouse_id'] ?? null);

        // Auto-generate production order number if not provided
        if (empty($data['order_number'])) {
            $batch = Batch::find($data['batch_id']);
            $data['order_number'] = $this->generateOrderNumber($batch);
        }

        // New runs always start as pending; QC officer will approve later
        if (! isset($data['status'])) {
            $data['status'] = 'confirmed';
        }
        $data['qc_status'] = 'pending';
        $run = ProductionRun::create($data);

        return redirect()->route('admin.production.index')->with('status', 'Production run recorded.');
    }

    public function update(Request $request, ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $user = auth()->user();

        $rules = [
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'supervisor_id' => 'nullable|exists:employees,id',
            'materials_reserved' => 'nullable|string',
        ];

        // Only admin/super admin and QC officer can change QC status
        if ($user?->hasAnyRole(['admin', 'super_admin', 'qc_officer'])) {
            $rules['qc_status'] = 'required|in:pending,approved,rejected';
        }

        $data = $request->validate($rules);

        $previousQcStatus = $production->qc_status;

        $production->update($data);

        // If QC just moved to approved for the first time, stamp approver + time
        if (array_key_exists('qc_status', $data)
            && $previousQcStatus !== 'approved'
            && $production->qc_status === 'approved') {
            if (! $production->approved_at) {
                $production->approved_at = now();
            }

            if (! $production->approved_by && auth()->check()) {
                $production->approved_by = auth()->id();
            }

            $production->save();
        }

        return redirect()->route('admin.production.index')->with('status', 'Production run updated.');
    }

    public function destroy(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);

        if ($production->stock_confirmed_at || $production->materialIssues()->exists()) {
            return redirect()
                ->route('admin.production.index')
                ->withErrors([
                    'production' => 'This production run has already posted inventory activity and cannot be deleted.',
                ]);
        }

        $production->delete();

        return redirect()->route('admin.production.index')->with('status', 'Production run deleted.');
    }

    /**
     * Confirm that stock from a QC-approved production run has been received
     * into the selected warehouse. Only admin / warehouse officer should do this.
     */
    public function confirmStock(Request $request, ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $user = auth()->user();

        if (! $user?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer'])) {
            return redirect()->route('admin.production.index')
                ->with('status', 'Only admin or warehouse officer can confirm stock.');
        }

        if ($production->qc_status !== 'approved') {
            return redirect()->route('admin.production.index')
                ->with('status', 'QC must be approved before confirming stock.');
        }

        if ($production->stock_confirmed_at) {
            return redirect()->route('admin.production.index')
                ->with('error', 'Stock already confirmed for this production run.');
        }

        DB::transaction(function () use ($production) {
            // Post finished goods + consume BOM materials atomically.
            $this->postStockForApprovedRun($production);

            $production->stock_confirmed_at = now();
            if (auth()->check()) {
                $production->stock_confirmed_by = auth()->id();
            }

            // Once stock is confirmed we can safely treat the production order / run
            // as completed from a process point of view.
            if (! in_array($production->status, ['cancelled'], true)) {
                $production->status = 'completed';
            }

            $production->save();
        });

        return redirect()->route('admin.production.index')
            ->with('status', 'Stock confirmed and posted to warehouse.');
    }

    protected function ensureWarehouseAccess($warehouseId): void
    {
        if ($warehouseId === null) {
            return;
        }

        if (! auth()->user()?->canAccessWarehouse((int) $warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    /**
     * Once a production run is QC approved, post finished goods stock and consume
     * BOM components from the selected warehouse.
     */
    protected function postStockForApprovedRun(ProductionRun $run): void
    {
        if ($run->quantity <= 0 || ! $run->warehouse_id) {
            return;
        }

        // Consume raw materials based on active BOM, if any
        $bom = BillOfMaterial::where('product_id', $run->product_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->with(['items.component'])
            ->first();

        if (! $bom || $bom->items->isEmpty()) {
            $entry = StockEntry::create([
                'warehouse_id' => $run->warehouse_id,
                'product_id' => $run->product_id,
                'batch_id' => $run->batch_id,
                'quantity' => $run->quantity,
                'status' => 'available',
            ]);

            StockMovement::recordFor(
                $entry,
                'production-output',
                (float) $run->quantity,
                'Confirmed from production run ' . ($run->order_number ?? ('#' . $run->id))
            );

            return;
        }

        $requirements = [];
        foreach ($bom->items as $item) {
            $component = $item->component;
            $totalRequired = (float) $item->quantity * (float) $run->quantity;

            if ($totalRequired <= 0) {
                continue;
            }

            if (! $component || ! $component->isStockTracked()) {
                throw ValidationException::withMessages([
                    'materials' => ['The active BOM contains a non-stock or missing component and cannot be confirmed to stock.'],
                ]);
            }

            if ((int) $component->id === (int) $run->product_id) {
                throw ValidationException::withMessages([
                    'materials' => ['The active BOM contains the same product as both finished good and component.'],
                ]);
            }

            $entries = StockEntry::where('warehouse_id', $run->warehouse_id)
                ->where('product_id', $item->component_product_id)
                ->where('status', 'available')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            $available = (float) $entries->sum('quantity');
            if ($available < $totalRequired) {
                $warehouseName = $run->warehouse?->name
                    ?? Warehouse::query()->whereKey($run->warehouse_id)->value('name')
                    ?? ('Warehouse #' . $run->warehouse_id);
                $uom = $component?->uom ? ' ' . $component->uom : '';

                throw ValidationException::withMessages([
                    'materials' => [
                        'Insufficient available stock for component '
                        . ($component->name ?? ('#' . $item->component_product_id))
                        . ' in '
                        . $warehouseName
                        . '. Required '
                        . number_format($totalRequired, 2)
                        . $uom
                        . ', available '
                        . number_format($available, 2)
                        . $uom
                        . '. Receive or transfer more stock before confirming production.',
                    ],
                ]);
            }

            $requirements[] = [
                'bom_item' => $item,
                'component' => $component,
                'entries' => $entries,
                'required' => $totalRequired,
            ];
        }

        $materialIssue = ProductionMaterialIssue::create([
            'production_run_id' => $run->id,
            'warehouse_id' => $run->warehouse_id,
            'issued_by' => auth()->id(),
            'issued_at' => now(),
            'notes' => 'Auto-issued during stock confirmation for production order ' . ($run->order_number ?? ('#' . $run->id)),
        ]);

        // If BOM has an explicit override material_unit_cost, use that directly.
        if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
            $unitCostAccumulator = (float) $bom->material_unit_cost;
        } else {
            $unitCostAccumulator = 0.0;
        }

        foreach ($requirements as $requirement) {
            $item = $requirement['bom_item'];
            $component = $requirement['component'];
            $remaining = $requirement['required'];

            foreach ($requirement['entries'] as $entry) {
                if ($remaining <= 0) {
                    break;
                }

                $consume = min($remaining, (float) $entry->quantity);
                if ($consume <= 0) {
                    continue;
                }

                $entry->quantity = (float) $entry->quantity - $consume;
                if ((float) $entry->quantity <= 0) {
                    $entry->quantity = 0;
                    $entry->status = 'sold'; // treated as consumed in production
                }
                $entry->save();

                StockMovement::recordFor(
                    $entry,
                    'production-consumption',
                    $consume * -1,
                    'Consumed for production run ' . ($run->order_number ?? ('#' . $run->id))
                );

                $baseCost = null;
                if ($item->unit_cost !== null) {
                    $baseCost = (float) $item->unit_cost;
                } elseif ($component && $component->standard_cost !== null) {
                    $baseCost = (float) $component->standard_cost;
                }

                ProductionMaterialIssueItem::create([
                    'production_material_issue_id' => $materialIssue->id,
                    'component_product_id' => $item->component_product_id,
                    'batch_id' => $entry->batch_id,
                    'quantity' => $consume,
                    'unit_cost' => $baseCost,
                    'line_total' => $baseCost !== null ? ($consume * $baseCost) : null,
                ]);

                $remaining -= $consume;
            }

            if ($remaining > 0.00001) {
                throw ValidationException::withMessages([
                    'materials' => ['Unable to consume all required stock for component ' . ($component->name ?? ('#' . $item->component_product_id)) . '.'],
                ]);
            }

            // If BOM-level override not set, accumulate from components
            if ($bom->material_unit_cost === null || $bom->material_unit_cost <= 0) {
                // Cost contribution from this component for ONE finished unit
                // Prefer BOM-level custom unit_cost if provided; fall back to product standard_cost
                $baseCost = null;
                if ($item->unit_cost !== null) {
                    $baseCost = (float) $item->unit_cost;
                } elseif ($component && $component->standard_cost !== null) {
                    $baseCost = (float) $component->standard_cost;
                }
                if ($baseCost !== null) {
                    $unitCostAccumulator += $baseCost * (float) $item->quantity;
                }
            }
        }

        $entry = StockEntry::create([
            'warehouse_id' => $run->warehouse_id,
            'product_id' => $run->product_id,
            'batch_id' => $run->batch_id,
            'quantity' => $run->quantity,
            'status' => 'available',
        ]);

        StockMovement::recordFor(
            $entry,
            'production-output',
            (float) $run->quantity,
            'Confirmed from production run ' . ($run->order_number ?? ('#' . $run->id))
        );

        // Persist material cost snapshot on the production run so that future
        // reports / COGS calculations can use a stable value.
        if ($unitCostAccumulator > 0) {
            $run->material_unit_cost = $unitCostAccumulator;
            $run->material_total_cost = $unitCostAccumulator * (float) $run->quantity;
            $run->save();
        }
    }

    /**
     * HTTP endpoint: generate a new production order number for the given batch.
     * Used by the "Generate" button on the create form.
     */
    public function orderNumber(Request $request)
    {
        $batch = null;
        if ($request->filled('batch_id')) {
            $batch = Batch::find($request->input('batch_id'));
        }

        $orderNumber = $this->generateOrderNumber($batch);

        return response()->json(['order_number' => $orderNumber]);
    }

    /**
     * Generate a simple production order number like PO-YYYYMMDD-001
     * based on the batch production date (or today if not set).
     */
    protected function generateOrderNumber(?Batch $batch): string
    {
        $date = $batch && $batch->production_date
            ? $batch->production_date
            : now()->toDateString();

        $dateKey = \Illuminate\Support\Carbon::parse($date)->format('Ymd');
        $prefix = "PO-{$dateKey}-";

        $lastOrder = ProductionRun::whereDate('created_at', $date)
            ->whereNotNull('order_number')
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $nextSeq = 1;
        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder, $m)) {
            $nextSeq = ((int) $m[1]) + 1;
        }

        $suffix = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        return $prefix.$suffix;
    }
}
