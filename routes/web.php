<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BonSortiePdfController;

Route::get('/', function () {
    return view('welcome');
});

// PDF route for BonSortie
Route::get('/admin/bon-sorties/{bonSortie}/pdf', [BonSortiePdfController::class, 'show'])->name('bonSortie.pdf');
