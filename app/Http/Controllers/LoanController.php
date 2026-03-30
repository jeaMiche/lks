<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user  = auth()->user();
        $query = Loan::with(['user', 'reviewer', 'approver']);

        if ($user->role === 'borrower') {
            $query->where('user_id', $user->id);
        }

        $loans = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Loans/Index', [
            'loans'      => $loans,
            'userRole'   => $user->role,
            'canReview'  => Gate::allows('review-loan'),
            'canApprove' => Gate::allows('approve-loan'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('is-borrower');

        return Inertia::render('Loans/Create', [
            'maxLoan'  => auth()->user()->monthly_revenue * 3,
            'revenue'  => auth()->user()->monthly_revenue,
        ]);
    }

    public function store(StoreLoanRequest $request)
    {
        $user = User::find(auth()->id());

        $rateByTenor = [
            3  => 2,
            6  => 3,
            12 => 4,
            24 => 5,
            36 => 6,
            48 => 7,
            60 => 8,
        ];

        $rate = $rateByTenor[$request->tenor_months] ?? 12;

        Loan::create([
            ...$request->validated(),
            'user_id'       => $user->id,
            'created_by'    => $user->id,
            'interest_rate' => $rate,
        ]);

        return redirect()->route('loans.index')
            ->with('success', 'Pengajuan pinjaman berhasil dikirim!');
    }

    public function show(Loan $loan): Response
    {
        $this->authorizeView($loan);

        return Inertia::render('Loans/Show', [
            'loan'      => $loan->load(['user', 'reviewer', 'approver']),
            'userRole'  => auth()->user()->role,
            'canReview' => Gate::allows('review-loan'),
            'canApprove' => Gate::allows('approve-loan'),
        ]);
    }

    public function review(Loan $loan)
    {
        Gate::authorize('review-loan');
        abort_if($loan->status !== 'pending', 422, 'Status tidak valid untuk direview.');

        $loan->update([
            'status'      => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'updated_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Pinjaman berhasil direview.');
    }

    public function approve(Loan $loan)
    {
        Gate::authorize('approve-loan');
        abort_if($loan->status !== 'reviewed', 422, 'Pinjaman harus direview terlebih dahulu.');

        $loan->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'updated_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Pinjaman berhasil disetujui!');
    }

    public function reject(Loan $loan)
    {
        Gate::authorize('approve-loan');

        $loan->update([
            'status'     => 'rejected',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Pinjaman ditolak.');
    }

    // Private helper — Borrower hanya bisa lihat miliknya
    private function authorizeView(Loan $loan): void
    {
        $user = auth()->user();
        if ($user->role === 'borrower' && $loan->user_id !== $user->id) {
            abort(403);
        }
    }
}
