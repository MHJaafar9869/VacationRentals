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
            "number of rooms" => $this->number_of_rooms,
            "image" => $this->image,
            "city" => $this->city,
            "country" => $this->country,
            "address" => $this->address,
            "night rate" => $this->night_rate,
            "status" => $this->status,
            "created at" => $this->created_at,
            "modified at" => $this->updated_at,
            "category_id" => $this->category_id,

        ];
    }
}