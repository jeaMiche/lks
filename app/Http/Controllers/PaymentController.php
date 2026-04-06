<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{

    // ─── RIWAYAT PEMBAYARAN ────────────────────────────
    public function index(Loan $loan)
    {
        $loan->load(['payments.user']);

        return Inertia::render('Payment/Index', [
            'loan'     => $loan,                    // pinjaman aktif user
            'schedule' => $loan->installments,      // jadwal cicilan
            'payments' => $loan->payments,          // riwayat bayar
        ]);
    } 
    
    // ─── FORM PEMBAYARAN ───────────────────────────────
    public function create(Loan $loan)
    {
        $user = auth()->user();
        abort_if($loan->user_id !== $user->id, 403);

        return inertia('Loans/paymentForm', [
            'loan' => $loan->load('user')
        ]);
    }

    // ─── SIMPAN PEMBAYARAN ─────────────────────────────
    public function store(Request $request, Loan $loan)
    {
        $user = auth()->user();
        abort_if($loan->user_id !== $user->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'method' => ['nullable', 'string'],
        ]);

        // Buat record pembayaran
        $payment = LoanPayment::create([
            'loan_id'      => $loan->id,
            'user_id'      => $user->id,
            'amount'       => $validated['amount'],
            'payment_date' => now(),
            'method'       => $validated['method'] ?? 'transfer',
        ]);

        // Update saldo pinjaman
        $loan->outstanding_balance = ($loan->outstanding_balance ?? $loan->amount) - $payment->amount;

        if ($loan->outstanding_balance <= 0) {
            $loan->status = 'lunas';
            $loan->outstanding_balance = 0;
        }

        $loan->save();

        // Redirect eksplisit agar Inertia tidak stuck loading
        return redirect()->route('loans.show', $loan->id)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}
