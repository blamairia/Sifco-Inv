<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BonSortiePdfController;
use App\Http\Controllers\BonEntreePdfController;
use App\Http\Controllers\StockMovementPdfController;

Route::get('/', function () {
    return view('welcome');
});

// PDF route for BonSortie
Route::get('/admin/bon-sorties/{bonSortie}/pdf', [BonSortiePdfController::class, 'show'])->name('bonSortie.pdf');

// PDF route for BonEntree
Route::get('/admin/bon-entrees/{bonEntree}/pdf', [BonEntreePdfController::class, 'show'])->name('bonEntree.pdf');

// PDF route for StockMovement
Route::get('/admin/stock-movements/{stockMovement}/pdf', [StockMovementPdfController::class, 'show'])->name('stockMovement.pdf');
Route::get('/admin/stock-movements/export/filtered', [StockMovementPdfController::class, 'exportFiltered'])->name('stockMovements.exportFilteredPdf');
Route::get('/admin/stock-movements/export/multiple', [StockMovementPdfController::class, 'exportMultiple'])->name('stockMovements.exportMultiplePdf');
