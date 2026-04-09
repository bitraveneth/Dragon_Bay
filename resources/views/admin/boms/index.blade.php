@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Bill of Materials
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    BOM
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Define how many bottles, caps, labels, etc. are required to produce one unit of a finished product.
            </p>
        </div>
        <a href="{{ route('admin.boms.create') }}" 
           data-tour="boms-primary-action"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add BOM
        </a>
    </div>

    <!-- BOMs Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Manufacturing Recipes</h3>
                @if($boms->isNotEmpty())
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $boms->total() ?? $boms->count() }} {{ Str::plural('BOM', $boms->total() ?? $boms->count()) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">BOM Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Summary</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($boms as $bom)
                        @php
                            // Determine material cost per finished unit for this BOM
                            $bomUnitCost = $bom->material_unit_cost;
                            if (is_null($bomUnitCost)) {
                                $acc = 0.0;
                                foreach ($bom->items as $item) {
                                    if (!is_null($item->unit_cost)) {
                                        $acc += (float) $item->unit_cost * (float) $item->quantity;
                                    }
                                }
                                $bomUnitCost = $acc > 0 ? $acc : null;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                @if($bom->product)
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $bom->product->name }}
                                        </p>
                                        @if($bom->product->sku)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                SKU: {{ $bom->product->sku }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $bom->name ?: 'Default' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                            {{ $bom->items->count() }} {{ Str::plural('component', $bom->items->count()) }}
                                        </span>
                                        @if(!is_null($bomUnitCost))
                                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                                Cost: BDT {{ number_format($bomUnitCost, 2) }}/unit
                                            </span>
                                        @endif
                                    </div>
                                    <button type="button"
                                            data-bom-components-trigger="{{ $bom->id }}"
                                            class="inline-flex w-fit items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Components
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($bom->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.boms.edit', $bom) }}" 
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.boms.destroy', $bom) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Delete this BOM? This action cannot be undone.');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No BOMs defined</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                                        No Bill of Materials have been defined yet. Use the "Add BOM" button above to create your first manufacturing recipe.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($boms, 'links'))
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $boms->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Component Modals -->
@foreach($boms as $bom)
    @php
        $bomUnitCost = $bom->material_unit_cost;
        if (is_null($bomUnitCost)) {
            $acc = 0.0;
            foreach ($bom->items as $item) {
                if (!is_null($item->unit_cost)) {
                    $acc += (float) $item->unit_cost * (float) $item->quantity;
                }
            }
            $bomUnitCost = $acc > 0 ? $acc : null;
        }
    @endphp
    <div id="bom-components-modal-{{ $bom->id }}" 
         class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm dark:bg-gray-900/80"
         style="display: none;">
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-700 dark:bg-gray-900">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        BOM Components
                    </h3>
                    <div class="mt-1 flex items-center gap-2">
                        @if($bom->product)
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $bom->product->sku ?? '' }}{{ $bom->product->sku ? ' — ' : '' }}{{ $bom->product->name }}
                            </span>
                        @endif
                        @if($bom->name)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $bom->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <button type="button" 
                        data-bom-components-close="{{ $bom->id }}"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                @if($bom->items->isEmpty())
                    <div class="text-center py-8">
                        <div class="mx-auto w-16 h-16 mb-3 text-gray-300 dark:text-gray-700">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No components defined for this BOM.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        <!-- Component List -->
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($bom->items as $item)
                                @php
                                    $qty = rtrim(rtrim(number_format($item->quantity, 4), '0'), '.');
                                    $unitCost = $item->unit_cost;
                                    $lineCost = (!is_null($unitCost)) ? $unitCost * $item->quantity : null;
                                @endphp
                                <div class="flex items-start justify-between py-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $item->component?->name ?? '—' }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                {{ $qty }} {{ $item->unit }}
                                            </span>
                                        </div>
                                        @if($item->component?->sku)
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                SKU: {{ $item->component->sku }}
                                            </p>
                                        @endif
                                    </div>
                                    @if(!is_null($unitCost))
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                BDT {{ number_format($unitCost, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                × {{ $qty }} = BDT {{ number_format($lineCost, 2) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Cost Summary -->
                        @if(!is_null($bomUnitCost))
                            <div class="mt-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Material cost per finished unit
                                    </span>
                                    <span class="text-lg font-semibold text-brand-600 dark:text-brand-400">
                                        BDT {{ number_format($bomUnitCost, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                <button type="button" 
                        data-bom-components-close="{{ $bom->id }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Close
                </button>
                <a href="{{ route('admin.boms.edit', $bom) }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit BOM
                </a>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Open modal
    document.querySelectorAll('[data-bom-components-trigger]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-bom-components-trigger');
            const modal = document.getElementById('bom-components-modal-' + id);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }
        });
    });

    // Close modal
    document.querySelectorAll('[data-bom-components-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-bom-components-close');
            const modal = document.getElementById('bom-components-modal-' + id);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll('[id^="bom-components-modal-"]').forEach((modal) => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="bom-components-modal-"][style*="display: flex"]').forEach((modal) => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            });
        }
    });
});
</script>
@endpush
