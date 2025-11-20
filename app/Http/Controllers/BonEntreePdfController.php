<?php

namespace App\Http\Controllers;

use App\Models\BonEntree;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Http\Request;

class BonEntreePdfController
{
    public function show(BonEntree $bonEntree)
    {
        // eager load relationships used in the PDF view
        $bonEntree->load('bonEntreeItems.product', 'bonEntreeItems.roll', 'warehouse', 'sourceable');
        $fileName = 'BonEntree-' . ($bonEntree->bon_number ?? $bonEntree->id) . '.pdf';

        // Render the view and ensure proper encoding for DomPdf
        $html = view('bon_entrees.pdf', ['bonEntree' => $bonEntree])->render();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $pdf = DomPdf::loadHtml($html);
        $pdf->setPaper('A4', 'portrait');

        // Return inline PDF response
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }
}
