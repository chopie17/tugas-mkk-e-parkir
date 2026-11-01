<?php

declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Enums\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk preview kalkulasi tarif sebelum pembayaran.
 */
class PreviewFeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle_category' => ['required', new Enum(VehicleCategory::class)],
        ];
    }
}
