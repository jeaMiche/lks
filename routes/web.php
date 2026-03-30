<?php

use App\Http\Controllers\LoanController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Borrower: buat & lihat pinjaman sendiri
    Route::resource('loans', LoanController::class)
         ->middleware('role:borrower,analyst,admin');

    // Analyst: review pinjaman
    Route::patch('loans/{loan}/review', [LoanController::class, 'review'])
         ->name('loans.review')
         ->middleware('role:analyst,admin');

    // Admin: approve / reject / disbursed
    Route::patch('loans/{loan}/approve',  [LoanController::class, 'approve'])
         ->name('loans.approve')
         ->middleware('role:admin');

    Route::patch('loans/{loan}/reject',   [LoanController::class, 'reject'])
         ->name('loans.reject')
         ->middleware('role:admin');
});

require __DIR__.'/auth.php';