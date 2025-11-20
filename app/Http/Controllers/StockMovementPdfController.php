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
}
