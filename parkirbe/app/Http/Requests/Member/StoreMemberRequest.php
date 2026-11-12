<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk pendaftaran member baru.
 */
class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'company_name'     => 'nullable|string|max:255',
            'plate_number'     => 'nullable|string|max:20',
            'phone_number'     => 'nullable|string|max:25',
            'email'            => 'nullable|email|max:100',
            'vehicle_category' => ['required', new Enum(VehicleCategory::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Nama member wajib diisi.',
            'email.email'               => 'Format email tidak valid.',
            'vehicle_category.required' => 'Kategori kendaraan wajib dipilih.',
        ];
    }
}
