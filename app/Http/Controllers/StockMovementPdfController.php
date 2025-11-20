<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Http\Request;

class StockMovementPdfController
{
    public function show(StockMovement $stockMovement)
    {
        // eager load relations
        $stockMovement->load('product', 'warehouseFrom', 'warehouseTo', 'user', 'approvedBy');
        $fileName = 'StockMovement-' . ($stockMovement->movement_number ?? $stockMovement->id) . '.pdf';

        $html = view('stock_movements.pdf', ['stockMovement' => $stockMovement])->render();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $pdf = DomPdf::loadHtml($html);
        $pdf->setPaper('A4', 'portrait');

        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }

    public function exportMultiple(\Illuminate\Http\Request $request)
    {
        $ids = $request->query('ids', '');
        $idsArr = array_values(array_filter(explode(',', $ids)));
        $movements = StockMovement::whereIn('id', $idsArr)
            ->with('product', 'warehouseFrom', 'warehouseTo', 'user', 'approvedBy')
            ->orderBy('performed_at', 'desc')
            ->get();

        $fileName = 'StockMovements-Export-' . now()->format('YmdHis') . '.pdf';

        $html = view('stock_movements.multiple_pdf', ['movements' => $movements])->render();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $pdf = DomPdf::loadHtml($html);
        $pdf->setPaper('A4', 'portrait');

        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }

    public function exportFiltered(\Illuminate\Http\Request $request)
    {
        $query = StockMovement::with('product', 'warehouseFrom', 'warehouseTo', 'user', 'approvedBy');

        if ($request->has('movement_type')) {
            $types = (array) $request->query('movement_type');
            $query->whereIn('movement_type', $types);
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }
        if ($request->has('warehouse_id')) {
            $warehouse = $request->query('warehouse_id');
            if ($warehouse) {
                $query->where(function ($q) use ($warehouse) {
                    $q->where('warehouse_from_id', $warehouse)
                      ->orWhere('warehouse_to_id', $warehouse);
                });
            }
        }
        if ($request->has('status')) {
            $statuses = (array) $request->query('status');
            $query->whereIn('status', $statuses);
        }
        if ($request->has('from')) {
            $query->whereDate('performed_at', '>=', $request->query('from'));
        }
        if ($request->has('to')) {
            $query->whereDate('performed_at', '<=', $request->query('to'));
        }

        $movements = $query->orderBy('performed_at', 'desc')->get();

        $fileName = 'StockMovements-Filtered-' . now()->format('YmdHis') . '.pdf';
        $html = view('stock_movements.multiple_pdf', ['movements' => $movements])->render();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $pdf = DomPdf::loadHtml($html);
        $pdf->setPaper('A4', 'portrait');

        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }
}
