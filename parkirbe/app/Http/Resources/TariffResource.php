<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** JSON Resource untuk model Tariff. */
class TariffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type->value,
            'vehicle_category' => $this->vehicle_category->value,
            'amount'           => $this->amount,
        ];
    }
}
