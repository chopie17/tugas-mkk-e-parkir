<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** JSON Resource untuk model MemberBill. */
class MemberBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'member_id'      => $this->member_id,
            'period'         => $this->period,
            'amount'         => $this->amount,
            'status'         => $this->status->value,
            'cash_received'  => $this->cash_received,
            'change_amount'  => $this->change_amount,
            'paid_at'        => $this->paid_at?->toIso8601String(),
            'processed_by'   => $this->processed_by,
            'invoice_number' => $this->invoice_number,
            'member'         => new MemberResource($this->whenLoaded('member')),
            'processor'      => new UserResource($this->whenLoaded('processor')),
        ];
    }
}
