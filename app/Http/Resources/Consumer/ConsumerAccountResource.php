<?php

namespace App\Http\Resources\Consumer;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ConsumerAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'whatsapp' => $this->whatsapp,
            'role' => $this->role instanceof \BackedEnum ? $this->role->value : $this->role,
            'provider' => $this->provider,
        ];
    }
}
