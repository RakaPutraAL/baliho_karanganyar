<?php

use App\Http\Controllers\BalihoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Baliho;   

Route::get('/', function () {
    $balihos = Baliho::with(['kecamatan','opd'])->get();

    return Inertia::render('Welcome', [
        'balihos' => $balihos,
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/balihos', [BalihoController::class, 'index'])->name('balihos.index');
    Route::get('/balihos/create', [BalihoController::class, 'create'])->name('balihos.create');
    Route::post('/balihos', [BalihoController::class, 'store'])->name('balihos.store');
    Route::get('/balihos/{baliho}/edit', [BalihoController::class, 'edit'])->name('balihos.edit');
    Route::put('balihos/{baliho}', [BalihoController::class, 'update']) ->name('balihos.update');
    Route::delete('/balihos/{baliho}', [BalihoController::class, 'destroy'])->name('balihos.destroy');
    Route::get('/maps', [BalihoController::class, 'maps'])->name('maps.index');
    Route::get('/balihos/export', [BalihoController::class, 'export'])->name('balihos.export');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
