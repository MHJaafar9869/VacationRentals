<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDatesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'booking_id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'guest_name' => $this->user->name
        ];
    }
}
