<?php

namespace App\Http\Controllers;

use App\Models\BonSortie;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Http\Request;

class BonSortiePdfController
{
    public function show(BonSortie $bonSortie)
    {
        // eager load relationships used in the PDF view
        $bonSortie->load('bonSortieItems.product', 'bonSortieItems.roll', 'warehouse', 'issuedBy');
        $fileName = 'BonSortie-' . ($bonSortie->bon_number ?? $bonSortie->id) . '.pdf';

        // Render the view and ensure proper encoding for DomPdf
        $html = view('bon_sorties.pdf', ['bonSortie' => $bonSortie])->render();
        // Convert to HTML Entities to be safe for DomPDF
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        // Load HTML directly to avoid issues with AJAX JSON responses
        $pdf = DomPdf::loadHtml($html);

        // Set a safe default paper and orientation
        $pdf->setPaper('A4', 'portrait');

        // Render and return inline (open in browser) response
        // Attachment => 0 tells Dompdf to render inline in the browser instead of forcing a download
        return $pdf->stream($fileName);
    }
}
