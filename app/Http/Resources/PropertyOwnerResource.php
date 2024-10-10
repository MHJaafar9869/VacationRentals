<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyOwnerResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'property_name' => $this->name,
            'night_rate' => $this->night_rate,
            'status' => $this->status,
            'location' => $this->location,
            'category' => new CategoryResource($this->category),
            'bookings' => BookingResource::collection($this->booking),
            'booking_count' => $this->booking->count()
        ];
    }
}