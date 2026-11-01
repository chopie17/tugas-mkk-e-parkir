<?php

declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Enums\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk pembayaran tiket parkir non-member.
 */
class PayTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle_category' => ['required', new Enum(VehicleCategory::class)],
            'plate_number'     => 'required|string|max:20',
            'cash_received'    => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_category.required' => 'Kategori kendaraan wajib dipilih.',
            'plate_number.required'     => 'Plat nomor wajib diisi.',
            'cash_received.required'    => 'Jumlah uang tunai wajib diisi.',
            'cash_received.integer'     => 'Jumlah uang tunai harus berupa angka.',
        ];
    }
}
