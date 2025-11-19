@php
    $companyName = config('app.name');
    $logoPath = public_path('logo.svg');
    $logoData = null;
    if (file_exists($logoPath)) {
        $svgContent = file_get_contents($logoPath);
        $logoData = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
    }
    $date = $bonSortie->issued_date ? $bonSortie->issued_date->format('d/m/Y') : now()->format('d/m/Y');
    $items = $bonSortie->bonSortieItems;
    $rollItems = $items->where('item_type', 'roll');
    $productItems = $items->where('item_type', 'product');
    $totalValue = $items->sum(fn($it) => ($it->qty_issued * ($it->cump_at_issue ?? 0)));
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
        <strong>Bobines</strong>
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

        <strong>Produits</strong>
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
                    <td class="total">Total</td>
                    <td class="right total">{{ number_format($totalValue, 2) }} DZD</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Sortie - {{ $bonSortie->bon_number ?? $bonSortie->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { display:flex; justify-content:space-between; margin-bottom: 12px; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table th, table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .small { font-size: 11px; color: #555; }
        .right { text-align: right; }
        .totals { margin-top: 8px; float:right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Bon de Sortie</h1>
            <div class="small">N°: {{ $bonSortie->bon_number ?? '-' }}</div>
            <div class="small">Date: {{ optional($bonSortie->issued_date)->format('d/m/Y') ?? optional($bonSortie->created_at)->format('d/m/Y') }}</div>
        </div>
        <div>
            <div class="small">Entrepôt: {{ $bonSortie->warehouse?->name ?? '-' }}</div>
            <div class="small">Destination: {{ $bonSortie->destination ?? '-' }}</div>
            <div class="small">Statut: {{ $bonSortie->status }}</div>
        </div>
    </div>

    <h2>Bobines</h2>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Poids (kg)</th>
                <th>Longueur (m)</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonSortie->bonSortieItems()->where('item_type', 'roll')->get() as $item)
                <tr>
                    <td>{{ optional($item->roll?->product)->name ?? '-' }}</td>
                    <td class="right">{{ number_format($item->weight_kg ?? $item->roll?->weight ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($item->length_m ?? $item->roll?->length ?? 0, 2) }}</td>
                    <td class="right">{{ $item->qty_issued ?? 1 }}</td>
                    <td class="right">{{ number_format($item->cump_at_issue ?? $item->roll?->cump ?? 0, 2) }} DZD</td>
                    <td class="right">{{ number_format(($item->qty_issued ?? 1) * ($item->cump_at_issue ?? $item->roll?->cump ?? 0), 2) }} DZD</td>
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
                <th>Prix Unitaire</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bonSortie->bonSortieItems()->where('item_type', 'product')->get() as $item)
                <tr>
                    <td>{{ optional($item->product)->name ?? '-' }}</td>
                    <td class="right">{{ $item->qty_issued ?? 0 }}</td>
                    <td class="right">{{ number_format($item->cump_at_issue ?? 0, 2) }} DZD</td>
                    <td class="right">{{ number_format(($item->qty_issued ?? 0) * ($item->cump_at_issue ?? 0), 2) }} DZD</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $rollTotal = $bonSortie->bonSortieItems()->where('item_type', 'roll')->get()->reduce(fn($carry, $i) => $carry + (($i->qty_issued ?? 1) * ($i->cump_at_issue ?? $i->roll?->cump ?? 0)), 0);
        $productTotal = $bonSortie->bonSortieItems()->where('item_type', 'product')->get()->reduce(fn($carry, $i) => $carry + (($i->qty_issued ?? 0) * ($i->cump_at_issue ?? 0)), 0);
        $grandTotal = $rollTotal + $productTotal;
    @endphp

    <div class="totals">
        <table>
            <tbody>
                <tr>
                    <th>Total Bobines</th>
                    <td class="right">{{ number_format($rollTotal, 2) }} DZD</td>
                </tr>
                <tr>
                    <th>Total Produits</th>
                    <td class="right">{{ number_format($productTotal, 2) }} DZD</td>
                </tr>
                <tr>
                    <th>Grand Total</th>
                    <td class="right">{{ number_format($grandTotal, 2) }} DZD</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
