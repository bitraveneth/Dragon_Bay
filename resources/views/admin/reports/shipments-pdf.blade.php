<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 6px; }
        p { margin: 0 0 16px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>

    <table>
        <thead>
            @if($report === 'mode')
                <tr>
                    <th>Mode</th>
                    <th class="right">Shipments</th>
                    <th class="right">Chargeable KG</th>
                    <th class="right">Revenue</th>
                    <th class="right">Cost</th>
                    <th class="right">Profit</th>
                </tr>
            @elseif($report === 'weight')
                <tr>
                    <th>Shipment</th>
                    <th>Client</th>
                    <th class="right">Actual KG</th>
                    <th class="right">CBM</th>
                    <th class="right">Vol KG</th>
                    <th class="right">Charge KG</th>
                </tr>
            @else
                <tr>
                    <th>Shipment</th>
                    <th>Client</th>
                    <th>Mode</th>
                    <th class="right">Chargeable KG</th>
                    <th class="right">Revenue</th>
                    <th class="right">Cost</th>
                    <th class="right">Profit</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($rows as $row)
                @if($report === 'mode')
                    <tr>
                        <td>{{ strtoupper(str_replace('_', ' ', $row['mode'])) }}</td>
                        <td class="right">{{ number_format($row['shipments']) }}</td>
                        <td class="right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                        <td class="right">{{ number_format($row['revenue'], 2) }}</td>
                        <td class="right">{{ number_format($row['cost'], 2) }}</td>
                        <td class="right">{{ number_format($row['profit'], 2) }}</td>
                    </tr>
                @elseif($report === 'weight')
                    <tr>
                        <td>{{ $row['shipment']->shipment_no }}</td>
                        <td>{{ $row['client'] ?? '-' }}</td>
                        <td class="right">{{ number_format($row['actual_weight'], 3) }}</td>
                        <td class="right">{{ number_format($row['cbm'], 4) }}</td>
                        <td class="right">{{ number_format($row['volumetric_weight'], 3) }}</td>
                        <td class="right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $row['shipment']->shipment_no }}</td>
                        <td>{{ $row['client'] ?? '-' }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $row['mode'])) }}</td>
                        <td class="right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                        <td class="right">{{ number_format($row['revenue'], 2) }}</td>
                        <td class="right">{{ number_format($row['cost'], 2) }}</td>
                        <td class="right">{{ number_format($row['profit'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>
