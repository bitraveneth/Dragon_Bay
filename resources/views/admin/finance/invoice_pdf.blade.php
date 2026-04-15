<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            background: #ffffff;
            margin: 0;
        }
        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }
        .invoice {
            max-width: 720px;
            margin: 0 auto;
        }
        .header {
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
        .logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            background: #465fff;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }
        .text-muted {
            color: #6b7280;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 4px 6px;
        }
        th {
            text-align: left;
            border-bottom: 1px solid #d1d5db;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #4b5563;
        }
        td {
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .totals td {
            border: none;
        }
        .totals tr:last-child td {
            border-top: 1px solid #d1d5db;
            font-weight: 700;
        }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mb-2 { margin-bottom: 8px; }
    </style>
</head>
@php
    $appName = config('app.name');
    $appInitials = mb_strtoupper(mb_substr($appName, 0, 2));
    $baseCurrencyCode = \App\Support\Currency::baseCode();
    $currencyCode = $invoice->currency_code ?? $baseCurrencyCode;
    $exchangeRate = (float) ($invoice->exchange_rate ?? 1);
@endphp
<body>
    <div class="invoice">
        <div class="header">
            <div class="row">
                <div>
                    <span class="logo-circle">{{ $legalCompanyInitials ?? $appInitials }}</span>
                    <div style="margin-top:6px;">
                        <h2 style="font-size:13px; font-weight:600;">{{ $legalCompanyName ?? $appName }}</h2>
                        <p class="text-muted" style="font-size:10px;">Invoice &amp; Finance</p>
                    </div>
                </div>
                <div style="text-align:right;">
                    <h1 style="font-size:16px; font-weight:700; margin-bottom:4px;">Invoice {{ $invoice->number }}</h1>
                    <p class="text-muted">Order #{{ $invoice->order_id ?? '—' }} · {{ $invoice->order?->agent->name ?? 'Unassigned' }}</p>
                    <p class="text-muted">Issued: {{ optional($invoice->issued_at)->format('d M Y') }}</p>
                    @if($invoice->due_at)
                        <p class="text-muted">Due: {{ $invoice->due_at->format('d M Y') }}</p>
                    @endif
                    <p class="text-muted">Status: {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</p>
                    @if($currencyCode !== $baseCurrencyCode)
                        <p class="text-muted">Currency: {{ $currencyCode }} · 1 {{ $currencyCode }} = {{ number_format($exchangeRate, 6) }} {{ $baseCurrencyCode }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div>
                <h3 style="font-size:11px; font-weight:600; margin-bottom:4px;">Bill to</h3>
                <p class="mt-2">
                    {{ $invoice->order?->agent->name ?? 'Customer' }}<br>
                </p>
            </div>
        </div>

        {{-- Items table --}}
        <table class="mt-4">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:45%;">Description</th>
                <th class="text-right" style="width:10%;">Qty</th>
                <th class="text-right" style="width:20%;">Unit price ({{ $currencyCode }})</th>
                <th class="text-right" style="width:20%;">Line total ({{ $currencyCode }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

        {{-- Totals --}}
        <div class="mt-4" style="display:flex; justify-content:flex-end;">
            <table class="totals" style="width:auto; font-size:10px;">
                <tr>
                    <td class="text-right">Net total ({{ $currencyCode }}):</td>
                    <td class="text-right" style="width:80px;">{{ number_format($invoice->net_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">VAT ({{ $currencyCode }}):</td>
                    <td class="text-right">{{ number_format($invoice->vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Gross total ({{ $currencyCode }}):</td>
                    <td class="text-right">{{ number_format($grossTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Withholding ({{ $currencyCode }}):</td>
                    <td class="text-right">- {{ number_format($invoice->withholding, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Cash due ({{ $currencyCode }}):</td>
                    <td class="text-right">{{ number_format($cashTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Credits ({{ $currencyCode }}):</td>
                    <td class="text-right">- {{ number_format($creditsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Receipts ({{ $currencyCode }}):</td>
                    <td class="text-right">- {{ number_format($receiptsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Advances ({{ $currencyCode }}):</td>
                    <td class="text-right">- {{ number_format($advancesTotal ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right" style="padding-top:4px; font-weight:600;">Outstanding ({{ $currencyCode }}):</td>
                    <td class="text-right" style="padding-top:4px; font-weight:600;">
                        {{ number_format(max(0, $outstanding), 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
