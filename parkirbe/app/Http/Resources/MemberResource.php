<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** JSON Resource untuk model Member. */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'member_code'      => $this->member_code,
            'name'             => $this->name,
            'company_name'     => $this->company_name,
            'plate_number'     => $this->plate_number,
            'phone_number'     => $this->phone_number,
            'email'            => $this->email,
            'vehicle_category' => $this->vehicle_category->value,
            'monthly_fee'      => $this->monthly_fee,
            'is_active'        => $this->is_active,
            'parking_status'   => $this->parking_status ?? 'out',
            'last_entry_at'    => $this->last_entry_at?->toIso8601String(),
            'last_exit_at'     => $this->last_exit_at?->toIso8601String(),
            'created_by'       => $this->created_by,
            'created_at'       => $this->created_at?->toIso8601String(),
            'bills'            => MemberBillResource::collection($this->whenLoaded('bills')),
        ];
    }
}
