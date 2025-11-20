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

    $date = $stockMovement->performed_at ? $stockMovement->performed_at->format('d/m/Y') : now()->format('d/m/Y');
@endphp

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mouvement Stock - {{ $stockMovement->movement_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .title { font-weight: bold; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table th, table td { border: 1px solid #ddd; padding: 6px; font-size: 12px; }
        .right { text-align: right; }
        .total { font-weight: bold; }
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
            <div>Mouvement de Stock</div>
            <div>N°: {{ $stockMovement->movement_number }}</div>
        </div>
        <div class="right">
            <div>Date: {{ $date }}</div>
            <div>Type: {{ $stockMovement->movement_type }}</div>
            <div>Réf: {{ $stockMovement->reference_number ?? '—' }}</div>
        </div>
    </div>

    <div>
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
                    <td>{{ $stockMovement->product?->name ?? '—' }}</td>
                    <td>{{ $stockMovement->product?->code ?? '—' }}</td>
                    <td>{{ $stockMovement->warehouseFrom?->name ?? '—' }}</td>
                    <td>{{ $stockMovement->warehouseTo?->name ?? '—' }}</td>
                    <td class="right">{{ number_format($stockMovement->qty_moved ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($stockMovement->cump_at_movement ?? 0, 2) }} DZD</td>
                    <td class="right">{{ number_format($stockMovement->value_moved ?? 0, 2) }} DZD</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 8px;">
            <table>
                <tr>
                    <td class="total">Préparé par</td>
                    <td>{{ $stockMovement->user?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="total">Approuvé par</td>
                    <td>{{ $stockMovement->approvedBy?->name ?? '—' }} {{ $stockMovement->approved_at ? '(' . $stockMovement->approved_at->format('d/m/Y H:i') . ')' : '' }}</td>
                </tr>
                <tr>
                    <td class="total">Notes</td>
                    <td>{{ $stockMovement->notes ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
