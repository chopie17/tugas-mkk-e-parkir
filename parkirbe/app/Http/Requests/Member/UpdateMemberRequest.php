<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk update data member.
 */
class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $memberId = $this->route('member')->id;
        return [
            'name'             => 'sometimes|required|string|max:255',
            'plate_number'     => ['sometimes','required','string','max:20', Rule::unique('members')->ignore($memberId)],
            'vehicle_category' => ['sometimes','required', new Enum(VehicleCategory::class)],
            'is_active'        => 'sometimes|required|boolean',
        ];
    }
}
