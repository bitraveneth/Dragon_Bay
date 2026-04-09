@extends('layouts.app')

@section('content')
@php
    $showManufacturing = \App\Helpers\MenuHelper::isBusinessAreaVisible('manufacturing');
    $showEmployees = \App\Helpers\MenuHelper::isBusinessAreaVisible('employees');
    $showCrm = \App\Helpers\MenuHelper::isBusinessAreaVisible('crm');
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:p-8">
        <div class="pointer-events-none absolute inset-x-0 -top-24 h-44 bg-gradient-to-r from-brand-500/15 via-blue-500/10 to-indigo-500/15 blur-3xl"></div>
        <div class="relative flex flex-col gap-5">
            <div class="flex items-center gap-3 mb-1">
                <span class="bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 px-3 py-1 rounded-full text-sm font-medium">📖 ডকুমেন্টেশন</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-600 dark:text-gray-400 text-sm">সিস্টেম গাইড v2.1</span>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3">হেল্প & সিস্টেম গাইড</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl">এই পেইজটা আসলে আপনার <span class="font-semibold text-brand-600 dark:text-brand-400">ERP ম্যানুয়াল</span> – উপরে মেনু অনুযায়ী হেল্প, নিচে পুরো সিস্টেমের ফ্লো।</p>
                <div class="mt-4">
                    <a href="{{ route('admin.client-guide') }}" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                        Open Client User Guide
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Navigation Cards --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">🔍 দ্রুত নেভিগেশন</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
            <a href="#help-control" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/40 dark:to-brand-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-brand-100/70 dark:border-brand-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('products') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">Masters</span>
            </a>
            @if($showManufacturing)
            <a href="#help-manufacturing" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-light-50 to-blue-light-100 dark:from-blue-light-900/40 dark:to-blue-light-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-blue-light-100/70 dark:border-blue-light-500/40">
                    {!! \App\Helpers\MenuHelper::getIconSvg('manufacturing') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">Manufacturing</span>
            </a>
            @endif
            <a href="#help-inventory" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-success-50 to-success-100 dark:from-success-900/30 dark:to-success-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-success-100/70 dark:border-success-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('inventory') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">Operations</span>
            </a>
            <a href="#help-sales" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-orange-100/70 dark:border-orange-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('sales') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">Orders</span>
            </a>
            <a href="#help-accounting" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-purple-100/70 dark:border-purple-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('accounting') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">Finance</span>
            </a>
            @if($showEmployees)
            <a href="#help-employees" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/40 dark:to-gray-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-gray-100/70 dark:border-gray-700/40">
                    {!! \App\Helpers\MenuHelper::getIconSvg('default') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">HR</span>
            </a>
            @endif
            @if($showCrm)
            <a href="#help-crm" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-900/30 dark:to-pink-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-pink-100/70 dark:border-pink-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('reports') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">CRM</span>
            </a>
            @endif
            <a href="#help-system" class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all hover:-translate-y-0.5">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-900/10 text-brand-600 dark:text-brand-300 shadow-theme-xs border border-indigo-100/70 dark:border-indigo-500/30">
                    {!! \App\Helpers\MenuHelper::getIconSvg('system') !!}
                </span>
                <span class="text-xs font-medium text-center text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400">System</span>
            </a>
        </div>
    </div>

    {{-- Main Help Sections with Tabs Design --}}
    <div class="space-y-6">
        <!-- Control Section -->
        <div id="help-control" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="control-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('products') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">1. Masters & Setup</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Catalog, clients, hubs, routes, and system access setup</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="control-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6 space-y-6">
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/control/control-overview.png</code>
                    </div>
                    <!-- Tax & VAT -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-600 dark:text-brand-400 flex-shrink-0">💰</div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Tax & VAT classes</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Masters → Catalog → Tax & VAT classes এ VAT rate, HSN/SAC এবং local tax code সেট করুন। Catalog item এ class সিলেক্ট করলে VAT auto calculate হবে।</p>
                            <div class="mt-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-2">
                                <img
                                    src="{{ asset('images/help/tax/tax-vat-classes.png') }}"
                                    alt="Tax and VAT classes screenshot"
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700"
                                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                                >
                                <div class="hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-3 text-xs text-gray-500 dark:text-gray-400">
                                    Screenshot not found. Add your image at <code>public/images/help/tax/tax-vat-classes.png</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Packaging types -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-600 dark:text-brand-400 flex-shrink-0">📦</div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Packaging types</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Masters → Catalog → Packaging types এ parcel, carton, crate-এর ইউনিট ও বর্ণনা সেট করুন। এই কনফিগারেশন pick list, dispatch slip, এবং operations view-তে দেখাবে।</p>
                            <div class="mt-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-2">
                                <img
                                    src="{{ asset('images/help/products/packaging-types.png') }}"
                                    alt="Packaging types screenshot"
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700"
                                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                                >
                                <div class="hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-3 text-xs text-gray-500 dark:text-gray-400">
                                    Screenshot not found. Add your image at <code>public/images/help/products/packaging-types.png</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Catalog -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-600 dark:text-brand-400 flex-shrink-0">🏷️</div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Catalog</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Masters → Catalog – প্রতিটি service/item এর জন্য:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <span class="bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded">SKU, নাম, Size, Volume</span>
                                <span class="bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded">Packaging type, Tax class</span>
                                <span class="bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded">Rate card / service classification</span>
                                <span class="bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded">Barcode / internal reference</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Warehouses -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-600 dark:text-brand-400 flex-shrink-0">🏢</div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Warehouses & Locations</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Masters → Hubs & warehouses – origin, transit, and destination hubs তৈরি করুন, Location code (R1-S2-B3) দিয়ে rack location সেট করুন।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($showManufacturing)
        <!-- Manufacturing Section -->
        <div id="help-manufacturing" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="manufacturing-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('manufacturing') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Manufacturing</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">BOM, Production orders & Batches</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="manufacturing-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6 space-y-6">
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/manufacturing/manufacturing-overview.png</code>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/10 dark:to-gray-800 p-4 rounded-xl border border-blue-100 dark:border-blue-900/20">
                            <div class="text-blue-600 dark:text-blue-400 text-2xl mb-2">📋</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">BOMs</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Bill of Materials – ১ cartoon 1L water = ১২ bottle + ১২ cap + ১২ label + ১ carton box</p>
                            <span class="inline-block mt-2 text-xs font-medium text-blue-600 dark:text-blue-400">Manufacturing → BOMs</span>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/10 dark:to-gray-800 p-4 rounded-xl border border-blue-100 dark:border-blue-900/20">
                            <div class="text-blue-600 dark:text-blue-400 text-2xl mb-2">🔖</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Batches & lots</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Batch code (1L-250105-A), Production date, Expiry date, QC status রেকর্ড করুন</p>
                            <span class="inline-block mt-2 text-xs font-medium text-blue-600 dark:text-blue-400">Manufacturing → Batches</span>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/10 dark:to-gray-800 p-4 rounded-xl border border-blue-100 dark:border-blue-900/20">
                            <div class="text-blue-600 dark:text-blue-400 text-2xl mb-2">⚙️</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Production orders</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Finished product, Batch, Warehouse, Line, Shift → QC approved → Stock entries</p>
                            <span class="inline-block mt-2 text-xs font-medium text-blue-600 dark:text-blue-400">Manufacturing → Production</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Inventory Section -->
        <div id="help-inventory" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="inventory-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('inventory') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Operations & Fulfillment</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Inbound, transfers, dispatch, POD, fleet</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="inventory-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6">
                    <div class="mb-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/inventory/inventory-overview.png</code>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">📊</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Operations dashboard</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">warehouse + product + batch অনুযায়ী available / reserved stock</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">🔄</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Transfers</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">এক warehouse থেকে অন্য warehouse এ stock পাঠান</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">🚚</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Dispatch & POD</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Route, Vehicle, Driver, Status, POD photo</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">📄</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Dispatch slips</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">One click এ packing slip প্রিন্ট</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">🚛</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Fleet management</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">গাড়ি, capacity, driver, daily schedule</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-green-600 dark:text-green-400 text-xl">⚖️</span>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Inventory adjustments</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Expired/Wasted/Return write-off</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Section -->
        <div id="help-sales" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="sales-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('sales') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Orders & Clients</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Clients, orders, commissions, delivery flow</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="sales-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6">
                    <div class="mb-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/sales/sales-overview.png</code>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-sm flex-shrink-0 mt-0.5">1</div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Clients</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Client/agent profile: Name, Area, Zone, Location code, Credit limit, KYC documents</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-sm flex-shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Rate cards & Commission</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Base price, special price per SKU, commission rules (২%)</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-sm flex-shrink-0 mt-0.5">3</div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Client orders flow</h4>
                                <ol class="list-decimal pl-5 text-sm text-gray-600 dark:text-gray-400 mt-1 space-y-1">
                                    <li>Agent নির্বাচন</li>
                                    <li>Order type & Delivery date</li>
                                    <li>SKU যোগ করুন – Qty, Unit price</li>
                                    <li>Save → Order Confirmed → Stock Reserved</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($showEmployees)
        <!-- Employees Section -->
        <div id="help-employees" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="employees-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('default') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Employees / HR</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">HR, Contracts, Allowances, Locations</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="employees-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6">
                    <div class="mb-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/employees/employees-overview.png</code>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">👤</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Employees</h4>
                            <p class="text-xs text-gray-500">Name, Department, Position, Zone</p>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">📄</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Contracts</h4>
                            <p class="text-xs text-gray-500">Salary, TA/DA, Bonus, Working schedule</p>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">💸</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Allowances</h4>
                            <p class="text-xs text-gray-500">TA/DA/BONUS claims with attachment</p>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">🏖️</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Leaves</h4>
                            <p class="text-xs text-gray-500">Apply/approve, remaining days</p>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">📱</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Equipment</h4>
                            <p class="text-xs text-gray-500">Device IMEI, status tracking</p>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <span class="text-orange-500 text-lg">📍</span>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mt-1">Locations</h4>
                            <p class="text-xs text-gray-500">GPS/manual location logs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Accounting Section -->
        <div id="help-accounting" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="accounting-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white">
                        {!! \App\Helpers\MenuHelper::getIconSvg('accounting') !!}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Finance & Billing</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Invoices, Receipts, Expenses, Reports</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="accounting-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6">
                    <div class="mb-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/accounting/accounting-overview.png</code>
                    </div>
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 p-4 rounded-xl mb-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">📌 <span class="font-semibold">AR (Accounts Receivable)</span> – গ্রাহকের কাছ থেকে পাওনা টাকা। Invoice তৈরি হলে AR ↑, টাকা পেলে AR ↓</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border-l-2 border-red-400 pl-3">
                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-1">🧾 Client invoices</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Delivered order থেকে invoice → AR ↑, Sales Revenue ↑, VAT Payable ↑</p>
                        </div>
                        <div class="border-l-2 border-red-400 pl-3">
                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-1">💵 Receipts</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">টাকা পেলে → Bank ↑, AR ↓</p>
                        </div>
                        <div class="border-l-2 border-red-400 pl-3">
                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-1">📝 Credit notes</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Discount/return → Sales Returns ↑, AR ↓</p>
                        </div>
                        <div class="border-l-2 border-red-400 pl-3">
                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-1">💰 Expenses</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Utilities, Marketing, Salary, Travel</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded text-center">
                            <span class="text-xs font-medium text-gray-900 dark:text-white">Profit & Loss</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded text-center">
                            <span class="text-xs font-medium text-gray-900 dark:text-white">Balance sheet</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded text-center">
                            <span class="text-xs font-medium text-gray-900 dark:text-white">Cashflow</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded text-center">
                            <span class="text-xs font-medium text-gray-900 dark:text-white">VAT report</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($showCrm)
        <!-- CRM Section -->
        <div id="help-crm" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="crm-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center text-white text-2xl">
                        🎯
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. CRM & Marketing</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Customer gifts, Campaigns</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="crm-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6">
                    <div class="mb-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/crm/crm-overview.png</code>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 bg-gradient-to-br from-pink-50 to-white dark:from-pink-900/10 dark:to-gray-800 p-4 rounded-xl">
                            <span class="text-3xl mb-2 block">🎁</span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Customer gifts</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">কোন এজেন্টকে কী ধরনের gift, কত টাকার, কোন campaign code এর against এ দেওয়া হলো</p>
                        </div>
                        <div class="flex-1 bg-gradient-to-br from-pink-50 to-white dark:from-pink-900/10 dark:to-gray-800 p-4 rounded-xl">
                            <span class="text-3xl mb-2 block">📢</span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Marketing campaigns</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Facebook/Instagram/Google ads, Field promo – reach, impressions, cost tracking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- System Section -->
        <div id="help-system" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 cursor-pointer section-trigger" data-target="system-content">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-2xl">
                        ⚙️
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">8. System Configuration (Live)</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cron, Queue, Notifications, Deploy checklist</p>
                    </div>
                </div>
                <button class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 transform transition-transform duration-200 section-arrow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div id="system-content" class="border-t border-gray-200 dark:border-gray-700 section-content hidden">
                <div class="p-6 space-y-6">
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                        Screenshot path: <code>public/images/help/system/system-overview.png</code>
                    </div>
                    <div class="bg-indigo-50/80 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700/40 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">🔔 Notification flow</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Business events or scheduled checks generate database notifications. Header unread counter and dropdown are fetched via AJAX from <code>/admin/notifications/header-data</code>. Mark-read actions update count instantly.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                            <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Scheduler</p>
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Active every minute</p>
                        </div>
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-800/40 dark:bg-blue-900/20">
                            <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">Queue</p>
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-200">Worker required</p>
                        </div>
                        <div class="rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 dark:border-purple-800/40 dark:bg-purple-900/20">
                            <p class="text-xs uppercase tracking-wide text-purple-700 dark:text-purple-300">Brand</p>
                            <p class="text-sm font-semibold text-purple-800 dark:text-purple-200">Controlled by APP_NAME</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cron (required)</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Run Laravel scheduler every minute:</p>
                            <div class="flex items-start gap-2">
                                <code id="cron-command" class="block flex-1 text-xs bg-gray-100 dark:bg-gray-900 rounded-lg p-2">* * * * * cd /var/www/saferpv1 && php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
                                <button type="button" class="js-copy-cmd h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-copy-target="cron-command">Copy</button>
                            </div>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Queue worker (recommended)</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">For queued notifications/jobs:</p>
                            <div class="flex items-start gap-2">
                                <code id="queue-command" class="block flex-1 text-xs bg-gray-100 dark:bg-gray-900 rounded-lg p-2">php artisan queue:work --tries=3</code>
                                <button type="button" class="js-copy-cmd h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-copy-target="queue-command">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Production ENV baseline</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-700 dark:text-gray-300">
                            <span class="bg-gray-50 dark:bg-gray-700/40 rounded px-2 py-1">APP_NAME=Your Brand Name</span>
                            <span class="bg-gray-50 dark:bg-gray-700/40 rounded px-2 py-1">APP_ENV=production</span>
                            <span class="bg-gray-50 dark:bg-gray-700/40 rounded px-2 py-1">APP_DEBUG=false</span>
                            <span class="bg-gray-50 dark:bg-gray-700/40 rounded px-2 py-1">QUEUE_CONNECTION=database</span>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Deploy command order</h4>
                        <ol class="list-decimal pl-5 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li><code>php artisan migrate --force</code></li>
                            <li><code>php artisan optimize:clear</code></li>
                            <li><code>php artisan config:cache && php artisan route:cache && php artisan view:cache</code></li>
                            <li><code>php artisan storage:link</code> (skip if exists)</li>
                        </ol>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Help Screenshot Paths</h4>
                        <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            <p><code>public/images/help/control/control-overview.png</code></p>
                            <p><code>public/images/help/tax/tax-vat-classes.png</code></p>
                            <p><code>public/images/help/products/packaging-types.png</code></p>
                            @if($showManufacturing)
                            <p><code>public/images/help/manufacturing/manufacturing-overview.png</code></p>
                            @endif
                            <p><code>public/images/help/inventory/inventory-overview.png</code></p>
                            <p><code>public/images/help/sales/sales-overview.png</code></p>
                            @if($showEmployees)
                            <p><code>public/images/help/employees/employees-overview.png</code></p>
                            @endif
                            <p><code>public/images/help/accounting/accounting-overview.png</code></p>
                            @if($showCrm)
                            <p><code>public/images/help/crm/crm-overview.png</code></p>
                            @endif
                            <p><code>public/images/help/system/system-overview.png</code></p>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">System Cycle Steps</h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Simple list</span>
                        </div>
                        <ol class="space-y-2">
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">1.</span> Configure masters</li>
                            @if($showManufacturing)
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">2.</span> Set BOM and production setup</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">3.</span> Purchase and receive materials</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">4.</span> Run production, QC, stock confirm</li>
                            @endif
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">5.</span> Manage inventory operations</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">6.</span> Process sales order lifecycle</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">7.</span> Deliver and capture POD</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">8.</span> Invoice, receipt, reconcile</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">9.</span> Handle returns and credit notes</li>
                            @if($showEmployees)
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">10.</span> Track HR and field activities</li>
                            @endif
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">11.</span> Generate and manage notifications</li>
                            <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-3 text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold text-brand-600 dark:text-brand-400">12.</span> Keep cron and queue running</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section toggle functionality (accordion: only one open at a time)
    const triggers = document.querySelectorAll('.section-trigger');
    const copyButtons = document.querySelectorAll('.js-copy-cmd');
    
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const content = document.getElementById(targetId);
            const arrow = this.querySelector('.section-arrow');

            if (!content) return;

            // Close all other sections first
            document.querySelectorAll('.section-content').forEach(section => {
                if (section.id !== targetId) {
                    section.classList.add('hidden');
                }
            });
            document.querySelectorAll('.section-arrow').forEach(icon => {
                if (icon !== arrow) {
                    icon.classList.remove('rotate-180');
                }
            });

            // Toggle the current section
            const willOpen = content.classList.contains('hidden');
            content.classList.toggle('hidden', !willOpen);
            if (arrow) {
                arrow.classList.toggle('rotate-180', willOpen);
            }
        });
    });

    copyButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const targetId = this.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;
            const text = target ? target.textContent.trim() : '';
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                const original = this.textContent;
                this.textContent = 'Copied';
                this.classList.add('text-emerald-600', 'dark:text-emerald-400');
                setTimeout(() => {
                    this.textContent = original;
                    this.classList.remove('text-emerald-600', 'dark:text-emerald-400');
                }, 1400);
            } catch (error) {
                this.textContent = 'Failed';
                setTimeout(() => {
                    this.textContent = 'Copy';
                }, 1400);
            }
        });
    });
    
    // Smooth scroll for navigation cards (with offset for fixed header)
    document.querySelectorAll('[href^="#help-"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Open the target section and close others (accordion behaviour)
                const content = targetElement.querySelector('.section-content');
                const arrow = targetElement.querySelector('.section-arrow');
                
                if (content) {
                    document.querySelectorAll('.section-content').forEach(section => {
                        if (section !== content) {
                            section.classList.add('hidden');
                        }
                    });
                    document.querySelectorAll('.section-arrow').forEach(icon => {
                        if (icon !== arrow) {
                            icon.classList.remove('rotate-180');
                        }
                    });

                    content.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-180');
                }

                // Scroll to the element with a dynamic top offset based
                // on the sticky app header height so the title is fully
                // visible below the navbar.
                const header = document.querySelector('header.sticky');
                const headerOffset = header ? header.offsetHeight + 16 : 96; // px
                const rect = targetElement.getBoundingClientRect();
                const offsetTop = rect.top + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    if (window.location.hash && window.location.hash.startsWith('#help-')) {
        const hashNav = document.querySelector(`[href="${window.location.hash}"]`);
        if (hashNav) {
            hashNav.click();
        }
    }
});
</script>
@endpush
@endsection
