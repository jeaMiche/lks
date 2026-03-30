<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'borrower';
    }

    public function rules(): array
    {
        $maxLoan = $this->user()->monthly_revenue * 3;

        return [
            'amount'        => ['required', 'numeric', 'min:1000000', "max:{$maxLoan}"],
            'tenor_months'  => ['required', 'integer', 'min:3', 'max:60'],
            'purpose'       => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'Jumlah pinjaman tidak boleh melebihi 3x omzet bulanan Anda (Maks: Rp ' 
                          . number_format($this->user()->monthly_revenue * 3, 0, ',', '.') . ')',
        ];
    }
}