<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'image' => $this->image,
            'address' => $this->address,
            'gender' => $this->gender,
            'payments' => PaymentResource::collection($this->payments),
            'favorites' => FavoriteResource::collection($this->favorites),
            'reviews' => ReviewResource::collection($this->reviews),
            'bookings' => BookingResource::collection($this->bookings)
        ];
    }
}