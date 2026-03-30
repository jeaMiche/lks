<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_by',
        'updated_by',
        'reviewed_by',
        'approved_by',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'monthly_installment'  => 'decimal:2',
        'reviewed_at'          => 'datetime',
        'approved_at'          => 'datetime',
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

    // ─── AMORTISASI (Rumus Flat — Mudah Dihafal!) ────────
    // Cicilan = (Pokok + Total Bunga) / Tenor
    public function calculateInstallment(): float
    {
        // Guard: kalau data kosong, return 0
        if (!$this->amount || !$this->tenor_months || !$this->interest_rate) {
            return 0;
        }

        $monthlyRate = $this->interest_rate / 100 / 12;

        // Kalau bunga 0%, pakai pembagian biasa
        if ($monthlyRate == 0) {
            return round($this->amount / $this->tenor_months, 2);
        }

        $factor = pow(1 + $monthlyRate, $this->tenor_months);

        // Guard: kalau factor - 1 = 0 (tidak mungkin tapi aman)
        if (($factor - 1) == 0) {
            return round($this->amount / $this->tenor_months, 2);
        }

        return round($this->amount * ($monthlyRate * $factor) / ($factor - 1), 2);
    }

    // ─── BOOT — Auto hitung cicilan sebelum disimpan ─────
    protected static function booted(): void
    {
        static::creating(function (Loan $loan) {
            $loan->monthly_installment = $loan->calculateInstallment();
        });

        static::updating(function (Loan $loan) {
            if ($loan->isDirty(['amount', 'tenor_months', 'interest_rate'])) {
                $loan->monthly_installment = $loan->calculateInstallment();
            }
        });
    }
}
