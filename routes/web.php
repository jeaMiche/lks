<?php

use App\Http\Controllers\LoanController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    // ── DASHBOARD ──
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ── AKSI KHUSUS LOANS (harus sebelum resource) ──
    Route::get('/profile/umkm',  [ProfileController::class, 'edit'])
        ->name('profile.umkm.edit')
        ->middleware('role:borrower');

    Route::patch('/profile/umkm', [ProfileController::class, 'update'])
        ->name('profile.umkm.update')
        ->middleware('role:borrower');

    Route::patch('loans/{loan}/review',   [LoanController::class, 'review'])
        ->name('loans.review')
        ->middleware('role:admin');

    Route::patch('loans/{loan}/approve',  [LoanController::class, 'approve'])
        ->name('loans.approve')
        ->middleware('role:admin');

    Route::patch('loans/{loan}/reject',   [LoanController::class, 'reject'])
        ->name('loans.reject')
        ->middleware('role:admin');

    Route::patch('loans/{loan}/disburse', [LoanController::class, 'disburse'])
        ->name('loans.disburse')
        ->middleware('role:admin');

    // ── PAYMENT (pakai LoanController) ──
    Route::get('loans/{loan}/pay',  [LoanController::class, 'paymentForm'])
        ->name('loans.payments.create')
        ->middleware('role:borrower');

    Route::post('loans/{loan}/pay', [LoanController::class, 'pay'])
        ->name('loans.payments.store')
        ->middleware('role:borrower');

    // routes/web.php — tambahkan di dalam group auth
    Route::get('loans/trashed', [LoanController::class, 'trashed'])
        ->name('loans.trashed')
        ->middleware('role:admin');

    Route::patch('loans/{id}/restore', [LoanController::class, 'restore'])
        ->name('loans.restore')
        ->middleware('role:admin');

    Route::delete('loans/{loan}', [LoanController::class, 'destroy'])
        ->name('loans.destroy')
        ->middleware('role:borrower,admin');

    // ── RESOURCE LOANS ──
    Route::resource('loans', LoanController::class)
        ->middleware('role:borrower,admin');
});

require __DIR__ . '/auth.php';
