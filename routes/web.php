<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\TrackController;
use App\Models\Record;
use Illuminate\Support\Facades\Route;

// Public catalog (read-only)
Route::get('/', [RecordController::class, 'index'])->name('records.index');
Route::get('/records/{record}', [RecordController::class, 'show'])->name('records.show');

// Public track library (read-only)
Route::get('/tracks', [TrackController::class, 'index'])->name('tracks.index');
Route::get('/tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');

// Post-login home. Staff are forwarded to the Filament admin panel; members
// get their own landing page. Keeping the redirect here means every auth flow
// (login, register, email verification) that targets route('dashboard') routes
// each role to the right place without duplicating the check per controller.
Route::get('/dashboard', function () {
    if (auth()->user()->role->isStaff()) {
        return redirect('/admin');
    }

    return view('dashboard', ['recordCount' => Record::count()]);
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
