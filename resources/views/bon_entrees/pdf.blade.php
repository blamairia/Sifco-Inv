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

    $date = $bonEntree->received_date ? $bonEntree->received_date->format('d/m/Y') : now()->format('d/m/Y');
    $items = $bonEntree->bonEntreeItems ?? collect([]);
    $rollItems = $items->where('item_type', 'bobine');
    $palletItems = $items->where('item_type', 'pallet');
    $productItems = $items->where('item_type', 'product');
    // Pre-calc totals
    $rollTotal = $rollItems->sum(fn($it) => ($it->qty_entered ?? 1) * ($it->price_ttc ?? $it->roll?->price ?? 0));
    $palletTotal = $palletItems->sum(fn($it) => ($it->qty_entered ?? 1) * ($it->price_ttc ?? 0));
    $productTotal = $productItems->sum(fn($it) => ($it->qty_entered ?? 0) * ($it->price_ttc ?? 0));
    $grandTotal = $rollTotal + $palletTotal + $productTotal;
@endphp

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon d'Entrée - {{ $bonEntree->bon_number }}</title>
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
            <div>Bon d'Entrée</div>
            <div>N°: {{ $bonEntree->bon_number }}</div>
        </div>
        <div class="right">
            <div>Date: {{ $date }}</div>
            <div>Entrepôt: {{ $bonEntree->warehouse?->name }}</div>
            <div>Origine: {{ $bonEntree->sourceable?->name ?? $bonEntree->document_number }}</div>
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
                    <th>Prix TTC</th>
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
                        <td class="right">{{ number_format($item->price_ttc ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->qty_entered ?? 0, 2) }}</td>
                        <td class="right">{{ number_format(($item->qty_entered * ($item->price_ttc ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Palettes</h2>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Largeur (mm)</th>
                    <th>Longueur (mm)</th>
                    <th>Quantité</th>
                    <th>Prix TTC</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($palletItems as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td class="right">{{ number_format($item->sheet_width_mm ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->sheet_length_mm ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->qty_entered ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->price_ttc ?? 0, 2) }}</td>
                        <td class="right">{{ number_format(($item->qty_entered * ($item->price_ttc ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Produits</h2>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Code</th>
                    <th>Quantité</th>
                    <th>Prix TTC</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productItems as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->product?->code ?? '-' }}</td>
                        <td class="right">{{ number_format($item->qty_entered ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->price_ttc ?? 0, 2) }}</td>
                        <td class="right">{{ number_format(($item->qty_entered * ($item->price_ttc ?? 0)), 2) }}</td>
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
                    <td class="total">Total Palettes</td>
                    <td class="right total">{{ number_format($palletTotal, 2) }} DZD</td>
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
