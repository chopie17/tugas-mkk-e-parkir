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
            'plate_number'     => $this->plate_number,
            'vehicle_category' => $this->vehicle_category->value,
            'monthly_fee'      => $this->monthly_fee,
            'is_active'        => $this->is_active,
            'created_by'       => $this->created_by,
            'created_at'       => $this->created_at?->toIso8601String(),
            'bills'            => MemberBillResource::collection($this->whenLoaded('bills')),
        ];
    }
}
