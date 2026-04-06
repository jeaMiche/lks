<?php

namespace App\Models;

use App\Models\LoanPayment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Loan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'tenor_months',
        'interest_rate',
        'status',
        'purpose',
        'monthly_installment',
        'outstanding_balance',
        'created_by',
        'updated_by',
        'reviewed_by',
        'approved_by',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'reviewed_at'         => 'datetime',
        'approved_at'         => 'datetime',
    ];

    // ─── RELASI ──────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    // ─── AMORTISASI ──────────────────────────────────────
    public function calculateInstallment(): float
    {
        if (!$this->amount || !$this->tenor_months || !$this->interest_rate) {
            return 0;
        }

        $r = $this->interest_rate / 100 / 12;
        $n = $this->tenor_months;
        $p = $this->amount;

        if ($r == 0) return round($p / $n, 2);

        $f = pow(1 + $r, $n);
        if (($f - 1) == 0) return round($p / $n, 2);

        return round($p * ($r * $f) / ($f - 1), 2);
    }

    // ─── BOOT ────────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Loan $loan) {
            $loan->monthly_installment = $loan->calculateInstallment();
            $loan->outstanding_balance = $loan->amount;
        });

        static::updating(function (Loan $loan) {
            if ($loan->isDirty(['amount', 'tenor_months', 'interest_rate'])) {
                $loan->monthly_installment = $loan->calculateInstallment();
            }

            // ─── AUDIT LOG ───────────────────────────────
            if ($loan->isDirty('status')) {
                Log::info('Loan status changed', [
                    'loan_id'    => $loan->id,
                    'old_status' => $loan->getOriginal('status'),
                    'new_status' => $loan->status,
                    'by_user'    => auth()->id(),
                    'at'         => now(),
                ]);
            }
        });

        static::deleted(function (Loan $loan) {
            Log::info('Loan soft deleted', [
                'loan_id' => $loan->id,
                'by_user' => auth()->id(),
                'at'      => now(),
            ]);
        });

        static::restored(function (Loan $loan) {
            Log::info('Loan restored', [
                'loan_id' => $loan->id,
                'by_user' => auth()->id(),
                'at'      => now(),
            ]);
        });
    }

    // ─── HELPER ──────────────────────────────────────────
    public static function hasActiveLoan(string $userId): bool
    {
        return self::where('user_id', $userId)
            ->whereIn('status', ['pending', 'reviewed', 'approved', 'disbursed'])
            ->exists();
    }

    public function isLunas(): bool
    {
        return ($this->outstanding_balance ?? 0) <= 0;
    }
}
