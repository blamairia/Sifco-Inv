@php
    $companyName = config('app.name');
    $logoData = null;
    $logoPngPath = public_path('logo.png');
    $logoSvgPath = public_path('logo.svg');
    if (extension_loaded('gd') && file_exists($logoPngPath)) {
        $pngContent = file_get_contents($logoPngPath);
        $logoData = 'data:image/png;base64,' . base64_encode($pngContent);
    } elseif (file_exists($logoSvgPath)) {
        $svgContent = file_get_contents($logoSvgPath);
        $logoData = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
    }

    $date = now()->format('d/m/Y');
@endphp

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Mouvement Stock - {{ $date }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .title { font-weight: bold; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table th, table td { border: 1px solid #ddd; padding: 6px; font-size: 12px; }
        .right { text-align: right; }
        .row-sep { margin-top: 8px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            @if($logoData)
                <div class="title"><img src="{{ $logoData }}" alt="{{ $companyName }}" style="height:48px;" /></div>
            @else
                <div class="title">{{ $companyName }}</div>
            @endif
            <div>Export Mouvement Stock</div>
            <div>Date: {{ $date }}</div>
        </div>
    </div>

    @foreach ($movements as $movement)
        <div class="row-sep">
            <h3>Mouvement: {{ $movement->movement_number }} ({{ $movement->movement_type }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Code</th>
                        <th>Depuis</th>
                        <th>Vers</th>
                        <th>Quantité</th>
                        <th>CUMP</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $movement->product?->name ?? '—' }}</td>
                        <td>{{ $movement->product?->code ?? '—' }}</td>
                        <td>{{ $movement->warehouseFrom?->name ?? '—' }}</td>
                        <td>{{ $movement->warehouseTo?->name ?? '—' }}</td>
                        <td class="right">{{ number_format($movement->qty_moved ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($movement->cump_at_movement ?? 0, 2) }} DZD</td>
                        <td class="right">{{ number_format($movement->value_moved ?? 0, 2) }} DZD</td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 6px;">
                <strong>Notes:</strong> {{ $movement->notes ?? '—' }}
            </div>
        </div>
    @endforeach

</body>
</html>
