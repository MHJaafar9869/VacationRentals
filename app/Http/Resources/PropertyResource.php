<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "headline" => $this->headline,
            "description" => $this->description,
            "amenities" => $this->amenities,
            "numberOfRooms" => $this->number_of_rooms,
            "image" => $this->image,
            "city" => $this->city,
            "country" => $this->country,
            "address" => $this->address,
            "nightRate" => $this->night_rate,
            "status" => $this->status,
            "createdAt" => $this->created_at,
            "modifiedAt" => $this->updated_at,
            "category_id" => $this->category_id,
            "property_type" => $this->category->name,
        ];
    }
}