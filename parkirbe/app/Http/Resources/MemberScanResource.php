<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** JSON Resource untuk hasil scan kartu member. */
class MemberScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'allowed'   => $this->resource['allowed'],
            'reason'    => $this->resource['reason'],
            'direction' => $this->resource['direction'] ?? 'in',
            'member'    => $this->resource['member']
                ? new MemberResource($this->resource['member'])
                : null,
        ];
    }
}
