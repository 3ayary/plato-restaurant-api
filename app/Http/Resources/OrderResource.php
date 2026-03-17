<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'user_name' => $this->user->name,
            'items'=> OrderItemResource::collection($this->items)
        ];
    }
}
