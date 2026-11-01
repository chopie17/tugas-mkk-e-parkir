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
            'plate_number'     => 'required|string|max:20|unique:members,plate_number',
            'vehicle_category' => ['required', new Enum(VehicleCategory::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Nama member wajib diisi.',
            'plate_number.required'     => 'Plat nomor wajib diisi.',
            'plate_number.unique'       => 'Plat nomor sudah terdaftar sebagai member.',
            'vehicle_category.required' => 'Kategori kendaraan wajib dipilih.',
        ];
    }
}
