<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// use App\Http\Controllers\ScraperController;

// Route::get('/', [ScraperController::class, 'index'])->name('home');
// Route::post('/scrape', [ScraperController::class, 'scrape'])->name('scrape');

use App\Http\Controllers\ImportController;

Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'handleForm'])->name('import.handle');
Route::post('/import/save', [ImportController::class, 'saveListing'])->name('import.save');