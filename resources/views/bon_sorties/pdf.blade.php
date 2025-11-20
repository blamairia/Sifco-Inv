@php
    $companyName = config('app.name');
    $logoData = null;
    $logoPngPath = public_path('logo.png');
    $logoSvgPath = public_path('logo.svg');
    // Prefer PNG only when the GD extension is available (Dompdf needs it to render PNG reliably)
    if (extension_loaded('gd') && file_exists($logoPngPath)) {
        $pngContent = file_get_contents($logoPngPath);
        $logoData = 'data:image/png;base64,' . base64_encode($pngContent);
    } elseif (file_exists($logoSvgPath)) {
        $svgContent = file_get_contents($logoSvgPath);
        $logoData = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
    }

    $date = $bonSortie->issued_date ? $bonSortie->issued_date->format('d/m/Y') : now()->format('d/m/Y');
    $items = $bonSortie->bonSortieItems ?? collect([]);
    $rollItems = $items->where('item_type', 'roll');
    $productItems = $items->where('item_type', 'product');
    // Pre-calc totals
    $rollTotal = $rollItems->sum(fn($it) => ($it->qty_issued ?? 1) * ($it->cump_at_issue ?? $it->roll?->cump ?? 0));
    $productTotal = $productItems->sum(fn($it) => ($it->qty_issued ?? 0) * ($it->cump_at_issue ?? 0));
    $grandTotal = $rollTotal + $productTotal;
@endphp

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Sortie - {{ $bonSortie->bon_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 16px; }
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
            <div>Bon de Sortie</div>
            <div>N°: {{ $bonSortie->bon_number }}</div>
        </div>
        <div class="right">
            <div>Date: {{ $date }}</div>
            <div>Entrepôt: {{ $bonSortie->warehouse?->name }}</div>
            <div>Destination: {{ $bonSortie->destination }}</div>
        </div>
    </div>

    <div>
        <h2>Bobines</h2>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Produit</th>
                    <th>Batch</th>
                    <th>Poids (kg)</th>
                    <th>Longueur (m)</th>
                    <th>CUMP (DZD)</th>
                    <th>Quantité</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rollItems as $item)
                    <tr>
                        <td>{{ $item->roll?->ean_13 ?? '-' }}</td>
                        <td>{{ $item->product?->name ?? ($item->roll?->product?->name ?? '-') }}</td>
                        <td>{{ $item->roll?->batch_number ?? '-' }}</td>
                        <td class="right">{{ number_format($item->weight_kg ?? 0, 3) }}</td>
                        <td class="right">{{ number_format($item->length_m ?? 0, 3) }}</td>
                        <td class="right">{{ number_format($item->cump_at_issue ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->qty_issued ?? 0, 2) }}</td>
                        <td class="right">{{ number_format(($item->qty_issued * ($item->cump_at_issue ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Produits</h2>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>CUMP (DZD)</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productItems as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td class="right">{{ number_format($item->qty_issued ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->cump_at_issue ?? 0, 2) }}</td>
                        <td class="right">{{ number_format(($item->qty_issued * ($item->cump_at_issue ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 8px; float: right;">
            <table>
                <tr>
                    <td class="total">Total Bobines</td>
                    <td class="right total">{{ number_format($rollTotal, 2) }} DZD</td>
                </tr>
                <tr>
                    <td class="total">Total Produits</td>
                    <td class="right total">{{ number_format($productTotal, 2) }} DZD</td>
                </tr>
                <tr>
                    <td class="total">Grand Total</td>
                    <td class="right total">{{ number_format($grandTotal, 2) }} DZD</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
<!-- consolidated template: remained content removed -->
