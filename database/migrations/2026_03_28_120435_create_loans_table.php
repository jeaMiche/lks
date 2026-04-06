<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── TABEL LOANS ───────────────────────────────
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->integer('tenor_months');
            $table->decimal('interest_rate', 5, 2)->default(12);

            $table->enum('status', [
                'pending',
                'reviewed',
                'approved',
                'rejected',
                'disbursed',
                'lunas' // ✅ tambahan status lunas
            ])->default('pending');

            $table->text('purpose');
            $table->decimal('monthly_installment', 15, 2)->nullable();
            $table->decimal('outstanding_balance', 15, 2)->nullable();

            $table->timestamp('disbursed_at')->nullable(); // kapan dicairkan
            $table->timestamp('end_date')->nullable();     // kapan tenor selesai

            // Audit Log
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // ─── TABEL LOAN_PAYMENTS ───────────────────────
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);       // nominal pembayaran
            $table->date('payment_date');           // tanggal bayar
            $table->enum('method', ['transfer', 'cash'])->default('transfer'); // metode

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
    }
};
