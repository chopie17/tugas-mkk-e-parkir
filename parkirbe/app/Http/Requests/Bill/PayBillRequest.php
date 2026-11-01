<?php

declare(strict_types=1);

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk pelunasan tagihan member.
 */
class PayBillRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cash_received' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'cash_received.required' => 'Jumlah uang tunai wajib diisi.',
            'cash_received.integer'  => 'Jumlah uang tunai harus berupa angka.',
        ];
    }
}
