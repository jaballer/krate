<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

// Public catalog (read-only)
Route::get('/', [RecordController::class, 'index'])->name('records.index');
Route::get('/records/{record}', [RecordController::class, 'show'])->name('records.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
