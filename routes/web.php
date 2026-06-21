<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// NOTE: email verification is intentionally NOT enforced yet — there is no real
// mail delivery wired, so gating on `verified` would wall new registrants behind
// an email link they can't receive. To enable later: implement MustVerifyEmail
// on App\Models\User, wire mail (Postmark), and add 'verified' back here.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
