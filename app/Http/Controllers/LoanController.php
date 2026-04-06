<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LoanController extends Controller
{
    // ─── DAFTAR PINJAMAN ───────────────────────────────
    public function index()
    {
        $user = auth()->user();

        $query = Loan::with('user')->orderByDesc('created_at');

        if ($user->role === 'borrower') {
            $query->where('user_id', $user->id);
        }

        $loans = $query->paginate(10);

        return inertia('Loans/Index', [
            'loans'        => $loans,
            'userRole'     => $user->role,
            'canReview'    => $user->role === 'admin',
            'canApprove'   => $user->role === 'admin',
            'canApplyLoan' => !Loan::hasActiveLoan($user->id),
        ]);
    }
    // ─── FORM CREATE ───────────────────────────────────
    public function create()
    {
        $user = auth()->user();

        // Cek pinjaman aktif — kalau ada, redirect balik
        if (Loan::hasActiveLoan($user->id)) {
            return redirect()->route('loans.index')
                ->with('error', 'Anda masih memiliki pinjaman aktif.');
        }

        return inertia('Loans/Create', [
            'maxLoan' => $user->monthly_revenue * 3,
            'revenue' => $user->monthly_revenue,
        ]);
    }

    // ─── STORE PENGAJUAN BARU ──────────────────────────
    public function store(Request $request)
    {
        $user = auth()->user();

        if (Loan::hasActiveLoan($user->id)) {
            return back()->withErrors([
                'amount' => 'Anda masih memiliki pinjaman aktif.'
            ]);
        }

        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:1000000', 'max:' . $user->monthly_revenue * 3],
            'tenor_months' => ['required', 'integer', 'in:3,6,12,24,36,48,60'],
            'purpose'      => ['required', 'string'],
        ]);

        $rateByTenor = [3 => 9, 6 => 10, 12 => 11, 24 => 12, 36 => 13, 48 => 14, 60 => 15];
        $rate = $rateByTenor[$validated['tenor_months']] ?? 12;

        Loan::create([
            ...$validated,
            'user_id'       => $user->id,
            'created_by'    => $user->id,
            'interest_rate' => $rate,
        ]);

        return redirect()->route('loans.index')
            ->with('success', 'Pengajuan pinjaman berhasil dikirim.');
    }

    // ─── DETAIL PINJAMAN ───────────────────────────────
    public function show(Loan $loan)
    {
        // Pastikan borrower hanya bisa lihat miliknya
        if (auth()->user()->role === 'borrower') {
            abort_if($loan->user_id !== auth()->id(), 403);
        }

        $loan->load('payments', 'user');

        return inertia('Loans/show', [
            'loan'     => $loan,
            'userRole' => auth()->user()->role,
        ]);
    }

    public function paymentForm(Loan $loan)
    {
        $user = auth()->user();
        abort_if($loan->user_id !== $user->id, 403);
        abort_if($loan->status !== 'disbursed', 403, 'Pinjaman belum dicairkan atau sudah lunas.');

        return inertia('Loans/paymentForm', [
            'loan' => $loan,
        ]);
    }

    // ─── BAYAR CICILAN ─────────────────────────────────
    public function pay(Request $request, Loan $loan)
    {
        $user = auth()->user();
        abort_if($loan->user_id !== $user->id, 403);
        abort_if($loan->status === 'lunas', 422, 'Pinjaman sudah lunas.');
        abort_if($loan->status !== 'disbursed', 403, 'Pinjaman belum dicairkan.');

        $request->validate([
            'method' => ['nullable', 'string', 'in:transfer,cash'],
        ]);

        $amount = min($loan->monthly_installment, $loan->outstanding_balance);

        $loan->payments()->create([
            'user_id'      => $user->id,
            'amount'       => $amount,
            'payment_date' => now()->toDateString(),
            'method'       => $request->method ?? 'transfer',
        ]);

        $loan->outstanding_balance = max($loan->outstanding_balance - $amount, 0);

        // ← update status jadi lunas kalau outstanding = 0
        if ($loan->outstanding_balance <= 0) {
            $loan->status              = 'lunas';
            $loan->outstanding_balance = 0;
        }

        $loan->save();

        return redirect()->route('loans.show', $loan->id)
            ->with('success', 'Pembayaran cicilan berhasil dicatat.');
    }
    
    // ─── APPROVE (ADMIN) ───────────────────────────────

    public function review(Loan $loan)
    {
        $loan->update(['status' => 'reviewed']);
        return back()->with('success', 'Pinjaman berhasil direview.');
    }

    public function approve(Loan $loan)
    {
        $loan->update(['status' => 'approved']);
        return back()->with('success', 'Pinjaman berhasil disetujui.');
    }

    // ─── REJECT (ADMIN) ────────────────────────────────
    public function reject(Loan $loan)
    {
        $loan->update(['status' => 'rejected']);
        return back()->with('success', 'Pinjaman ditolak.');
    }

    // ─── DISBURSE (ADMIN) ──────────────────────────────
    public function disburse(Loan $loan)
    {
        $loan->update([
            'status' => 'disbursed',
            'outstanding_balance' => $loan->amount,
        ]);
        return back()->with('success', 'Pinjaman berhasil dicairkan.');
    }

    // Soft delete — Admin only
    public function destroy(Loan $loan)
    {
        $user = auth()->user();

        if ($user->role === 'borrower') {
            // Borrower hanya bisa hapus miliknya sendiri yang masih pending
            abort_if($loan->user_id !== $user->id, 403);
            abort_if($loan->status !== 'pending', 403, 'Hanya pinjaman pending yang bisa dihapus.');
        } else {
            // Admin bisa hapus pending & rejected
            Gate::authorize('approve-loan');
            abort_if(!in_array($loan->status, ['pending', 'rejected']), 403, 'Status tidak bisa dihapus.');
        }

        $loan->update(['updated_by' => $user->id]);
        $loan->delete();

        return back()->with('success', 'Pinjaman berhasil dihapus.');
    }

    // Restore — Admin only
    public function restore(string $id)
    {
        Gate::authorize('approve-loan');

        $loan = Loan::withTrashed()->findOrFail($id);
        $loan->restore();
        $loan->update(['updated_by' => auth()->id()]);

        return back()->with('success', 'Pinjaman berhasil dipulihkan.');
    }

    // List soft deleted — Admin only
    public function trashed()
    {
        Gate::authorize('approve-loan');

        $loans = Loan::onlyTrashed()
            ->with('user')
            ->latest('deleted_at')
            ->paginate(10);

        return Inertia::render('Loans/Trashed', [
            'loans'    => $loans,
            'userRole' => auth()->user()->role,
        ]);
    }
}
