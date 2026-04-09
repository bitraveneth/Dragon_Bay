@extends('layouts.app')

@section('content')
@php
    $currencyOptions = [
        ['code' => 'AED', 'label' => 'AED · UAE Dirham'],
        ['code' => 'AUD', 'label' => 'AUD · Australian Dollar'],
        ['code' => 'BDT', 'label' => 'BDT · Bangladeshi Taka'],
        ['code' => 'CAD', 'label' => 'CAD · Canadian Dollar'],
        ['code' => 'CHF', 'label' => 'CHF · Swiss Franc'],
        ['code' => 'CNY', 'label' => 'CNY · Chinese Yuan'],
        ['code' => 'EUR', 'label' => 'EUR · Euro'],
        ['code' => 'GBP', 'label' => 'GBP · British Pound'],
        ['code' => 'HKD', 'label' => 'HKD · Hong Kong Dollar'],
        ['code' => 'INR', 'label' => 'INR · Indian Rupee'],
        ['code' => 'JPY', 'label' => 'JPY · Japanese Yen'],
        ['code' => 'KWD', 'label' => 'KWD · Kuwaiti Dinar'],
        ['code' => 'MYR', 'label' => 'MYR · Malaysian Ringgit'],
        ['code' => 'NPR', 'label' => 'NPR · Nepalese Rupee'],
        ['code' => 'PKR', 'label' => 'PKR · Pakistani Rupee'],
        ['code' => 'QAR', 'label' => 'QAR · Qatari Riyal'],
        ['code' => 'SAR', 'label' => 'SAR · Saudi Riyal'],
        ['code' => 'SGD', 'label' => 'SGD · Singapore Dollar'],
        ['code' => 'THB', 'label' => 'THB · Thai Baht'],
        ['code' => 'USD', 'label' => 'USD · US Dollar'],
    ];
    $settingsPageState = [
        'primary' => old('brand_primary_color', $settings['brand_primary_color']),
        'secondary' => old('brand_secondary_color', $settings['brand_secondary_color']),
        'defaultPrimary' => $defaultThemeColors['primary'],
        'defaultSecondary' => $defaultThemeColors['secondary'],
        'textLight' => old('text_color_light', $settings['text_color_light']),
        'textDark' => old('text_color_dark', $settings['text_color_dark']),
        'defaultTextLight' => $defaultThemeColors['textLight'],
        'defaultTextDark' => $defaultThemeColors['textDark'],
        'currencyCode' => old('currency_code', $settings['currency_code']),
        'currencySymbol' => old('currency_symbol', $settings['currency_symbol']),
        'currencySymbols' => [
            'AED' => 'د.إ',
            'AUD' => '$',
            'BDT' => '৳',
            'CAD' => '$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'EUR' => '€',
            'GBP' => '£',
            'HKD' => '$',
            'INR' => '₹',
            'JPY' => '¥',
            'KWD' => 'د.ك',
            'MYR' => 'RM',
            'NPR' => 'रू',
            'PKR' => '₨',
            'QAR' => 'ر.ق',
            'SAR' => '﷼',
            'SGD' => '$',
            'THB' => '฿',
            'USD' => '$',
        ],
    ];
    $currencySymbolOptions = collect($settingsPageState['currencySymbols'])
        ->map(fn ($symbol, $code) => ['code' => $code, 'symbol' => $symbol])
        ->values()
        ->all();
    $brandPreviewName = old('brand_name', $settings['brand_name'])
        ?: old('company_name', $settings['company_name'])
        ?: $settings['app_name'];
    $brandPreviewInitials = strtoupper(mb_substr(
        collect(preg_split('/[^A-Za-z0-9]+/', $brandPreviewName, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('') ?: $brandPreviewName,
        0,
        2
    ));
    $tabErrorMap = [
        'brand' => ['brand_name', 'company_name', 'company_email', 'company_phone', 'company_address', 'company_logo', 'remove_logo'],
        'currency' => ['currency_code', 'currency_symbol'],
        'colors' => ['brand_primary_color', 'brand_secondary_color', 'text_color_light', 'text_color_dark', 'default_theme_mode'],
        'sms' => ['sms_provider', 'sms_base_url', 'sms_api_key', 'sms_api_secret', 'sms_sender_id'],
        'smtp' => ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'mail_from_address', 'mail_from_name'],
        'database' => ['backup', 'filename', 'confirm_restore', 'restore'],
    ];
    $initialSettingsTab = 'brand';

    if (($errors ?? null) && $errors->any()) {
        $errorKeys = array_keys($errors->getMessages());

        foreach ($tabErrorMap as $tab => $fieldKeys) {
            if (collect($errorKeys)->contains(fn ($key) => in_array($key, $fieldKeys, true))) {
                $initialSettingsTab = $tab;
                break;
            }
        }
    }
@endphp
<div x-data="{
        activeTab: @js($initialSettingsTab),
        primary: @js($settingsPageState['primary']),
        secondary: @js($settingsPageState['secondary']),
        defaultPrimary: @js($settingsPageState['defaultPrimary']),
        defaultSecondary: @js($settingsPageState['defaultSecondary']),
        textLight: @js($settingsPageState['textLight']),
        textDark: @js($settingsPageState['textDark']),
        defaultTextLight: @js($settingsPageState['defaultTextLight']),
        defaultTextDark: @js($settingsPageState['defaultTextDark']),
        currencyCode: @js($settingsPageState['currencyCode']),
        currencySymbol: @js($settingsPageState['currencySymbol']),
        currencySymbols: @js($settingsPageState['currencySymbols']),
        resetThemeColors() {
            this.primary = this.defaultPrimary;
            this.secondary = this.defaultSecondary;
            this.textLight = this.defaultTextLight;
            this.textDark = this.defaultTextDark;
        },
        syncCurrencySymbol() {
            const mapped = this.currencySymbols[this.currencyCode];
            if (mapped) {
                this.currencySymbol = mapped;
            }
        }
    }"
    class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Application settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Keep branding, money settings, communication gateways, and backups in one place.
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <span class="font-semibold text-gray-900 dark:text-white">Live brand:</span>
            <span class="ml-2 inline-flex items-center gap-2">
                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: primary }"></span>
                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: secondary }"></span>
                {{ $appBrandName }}
            </span>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            <div class="font-semibold">Please review the form.</div>
            <ul class="mt-2 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="Application settings sections">
            <button type="button"
                    @click="activeTab = 'brand'"
                    :class="activeTab === 'brand'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                Brand
            </button>
            <button type="button"
                    @click="activeTab = 'colors'"
                    :class="activeTab === 'colors'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                Colors
            </button>
            <button type="button"
                    @click="activeTab = 'currency'"
                    :class="activeTab === 'currency'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                Currency
            </button>
            <button type="button"
                    @click="activeTab = 'sms'"
                    :class="activeTab === 'sms'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                SMS
            </button>
            <button type="button"
                    @click="activeTab = 'smtp'"
                    :class="activeTab === 'smtp'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                SMTP
            </button>
            <button type="button"
                    @click="activeTab = 'database'"
                    :class="activeTab === 'database'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="inline-flex shrink-0 items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                Database
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div x-show="activeTab === 'brand'" x-cloak class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900" data-tour="settings-brand-identity">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Names and contact details</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Choose the short brand label users see in the app, then add the full company details used in formal records.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Brand name</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings['brand_name']) }}"
                               placeholder="Short UI brand, e.g. SAF"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Used in sidebar, header, login, and compact brand labels.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company name</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Used for formal company references and documents.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company email</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company address</label>
                        <textarea name="company_address" rows="3"
                                  class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('company_address', $settings['company_address']) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="activeTab === 'colors'" x-cloak class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visual identity</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload the logo used across screens and documents, then adjust the main brand colors.</p>
                    </div>
                    <button type="button"
                            @click="resetThemeColors()"
                            class="inline-flex shrink-0 items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-300">
                        Reset default colors
                    </button>
                </div>

                    <div class="space-y-5">
                    <div class="rounded-2xl border border-dashed border-gray-200 p-5 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Company logo" class="h-18 w-18 rounded-2xl border border-gray-200 object-cover dark:border-gray-700" />
                            @else
                                <div class="flex h-18 w-18 items-center justify-center rounded-2xl text-lg font-semibold text-white"
                                     :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                    {{ $brandPreviewInitials }}
                                </div>
                            @endif

                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Current brand mark</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, or WebP up to 2MB. Recommended square logo: 512 × 512 px or larger.</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Upload logo</label>
                            <input type="file" name="company_logo"
                                   class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:file:bg-brand-500/10 dark:file:text-brand-300" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">For best results, use a centered square logo at 512 × 512 px. Transparent PNG works best.</p>
                        </div>

                        @if($logoUrl)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                Remove current logo
                            </label>
                        @endif
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Primary color</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="brand_primary_color" x-model="primary" value="{{ old('brand_primary_color', $settings['brand_primary_color']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="primary"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Secondary color</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="brand_secondary_color" x-model="secondary" value="{{ old('brand_secondary_color', $settings['brand_secondary_color']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="secondary"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Light mode text</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="text_color_light" x-model="textLight" value="{{ old('text_color_light', $settings['text_color_light']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="textLight"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dark mode text</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="text_color_dark" x-model="textDark" value="{{ old('text_color_dark', $settings['text_color_dark']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="textDark"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Default theme</label>
                            <select name="default_theme_mode"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @php $selectedThemeMode = old('default_theme_mode', $settings['default_theme_mode']); @endphp
                                <option value="dark" @selected($selectedThemeMode === 'dark')>Dark</option>
                                <option value="light" @selected($selectedThemeMode === 'light')>Light</option>
                                <option value="system" @selected($selectedThemeMode === 'system')>Follow device</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Used when the current browser has no saved theme preference yet.</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Live preview</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Preview updates before you save.</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: primary }"></span>
                                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: secondary }"></span>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between px-4 py-4 text-white"
                                 :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/18 text-sm font-semibold">
                                        {{ $brandPreviewInitials }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">{{ $brandPreviewName }}</div>
                                        <div class="text-xs text-white/80">Admin navigation and dashboards</div>
                                    </div>
                                </div>
                                <button type="button" class="rounded-lg bg-white/16 px-3 py-2 text-xs font-semibold">Action</button>
                            </div>
                            <div class="grid gap-3 bg-gray-50 p-4 dark:bg-gray-950/40">
                                <div class="rounded-xl bg-white p-4 shadow-theme-xs dark:bg-gray-900">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sidebar active item</div>
                                    <div class="mt-3 inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white"
                                         :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                        Settings
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="activeTab === 'currency'" x-cloak class="grid gap-6 xl:grid-cols-1">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Currency defaults</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">These values are used when reports and accounting screens need a default currency format.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Currency code</label>
                        <select name="currency_code"
                                x-model="currencyCode"
                                @change="syncCurrencySymbol()"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php $selectedCurrencyCode = old('currency_code', $settings['currency_code']); @endphp
                            @foreach($currencyOptions as $currency)
                                <option value="{{ $currency['code'] }}" {{ $selectedCurrencyCode === $currency['code'] ? 'selected' : '' }}>
                                    {{ $currency['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Currency symbol</label>
                        <select name="currency_symbol"
                                x-model="currencySymbol"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php $selectedCurrencySymbol = old('currency_symbol', $settings['currency_symbol']); @endphp
                            @foreach($currencySymbolOptions as $option)
                                <option value="{{ $option['symbol'] }}" {{ $selectedCurrencySymbol === $option['symbol'] ? 'selected' : '' }}>
                                    {{ $option['symbol'] }} · {{ $option['code'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="activeTab === 'sms'" x-cloak class="space-y-6">
            <div class="grid gap-6 xl:grid-cols-1">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">SMS gateway</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add provider details only if you use SMS alerts or OTP-style communication.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider name</label>
                        <input type="text" name="sms_provider" value="{{ old('sms_provider', $settings['sms_provider']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sender ID</label>
                        <input type="text" name="sms_sender_id" value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Base URL</label>
                        <input type="text" name="sms_base_url" value="{{ old('sms_base_url', $settings['sms_base_url']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">API key</label>
                        <input type="text" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">API secret</label>
                        <input type="password" name="sms_api_secret" value="{{ old('sms_api_secret', $settings['sms_api_secret']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                </div>
            </section>
            </div>
        </div>

        <div x-show="activeTab === 'smtp'" x-cloak class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP email</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Configure the outgoing mail server used for notifications, password emails, and system mail.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP host</label>
                    <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP port</label>
                    <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Encryption</label>
                    <select name="smtp_encryption"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">No encryption</option>
                        <option value="tls" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'ssl')>SSL</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP username</label>
                    <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP password</label>
                    <input type="password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mail from address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div class="md:col-span-2 xl:col-span-1">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mail from name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
            </div>
            </section>
        </div>

        <div x-show="activeTab !== 'database'" x-cloak class="flex items-center justify-end gap-3">
            <p class="mr-auto text-xs text-gray-500 dark:text-gray-400">Review all sections, then save once at the end.</p>
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                Save settings
            </button>
        </div>
    </form>

    <section x-show="activeTab === 'database'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Database backup and restore</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Create SQL snapshots of the live database, download them, and restore the system from a saved backup.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.settings.backups.create') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    Create backup
                </button>
            </form>
        </div>

        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
            Restoring a backup will replace the current database state. Use restore only when you intentionally want to roll back to a previous snapshot.
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-950/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Backup file</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Size</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse($backups as $backup)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $backup['filename'] }}</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">SQL snapshot stored in local backup storage.</div>
                                </td>
                                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-300">
                                    {{ $backup['last_modified_at']->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-300">
                                    {{ $backup['size_label'] }}
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.settings.backups.download', ['filename' => $backup['filename']]) }}"
                                           class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                            Download
                                        </a>
                                        <form method="POST" action="{{ route('admin.settings.backups.restore') }}"
                                              onsubmit="return confirm('Restore this backup and overwrite the current database?');">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                            <input type="hidden" name="confirm_restore" value="1">
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-xs font-semibold text-error-700 hover:bg-error-100 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                                                Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No database backups have been created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
