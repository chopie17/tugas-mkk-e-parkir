<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** JSON Resource untuk model ParkingTicket. */
class ParkingTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'token'            => $this->token,
            'entry_at'         => $this->entry_at?->toIso8601String(),
            'exit_at'          => $this->exit_at?->toIso8601String(),
            'status'           => $this->status->value,
            'vehicle_category' => $this->vehicle_category?->value,
            'plate_number'     => $this->plate_number,
            'duration_hours'   => $this->duration_hours,
            'fee'              => $this->fee,
            'cash_received'    => $this->cash_received,
            'change_amount'    => $this->change_amount,
            'processed_by'     => $this->processed_by,
            'invoice_number'   => $this->invoice_number,
            'processor'        => new UserResource($this->whenLoaded('processor')),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
