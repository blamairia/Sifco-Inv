<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BonSortiePdfController;
use App\Http\Controllers\BonEntreePdfController;

Route::get('/', function () {
    return view('welcome');
});

// PDF route for BonSortie
Route::get('/admin/bon-sorties/{bonSortie}/pdf', [BonSortiePdfController::class, 'show'])->name('bonSortie.pdf');

// PDF route for BonEntree
Route::get('/admin/bon-entrees/{bonEntree}/pdf', [BonEntreePdfController::class, 'show'])->name('bonEntree.pdf');
