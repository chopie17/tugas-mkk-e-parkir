<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON Resource untuk model User.
 * Password tidak pernah disertakan dalam respons.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'username'   => $this->username,
            'email'      => $this->email,
            'role'       => $this->role->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
