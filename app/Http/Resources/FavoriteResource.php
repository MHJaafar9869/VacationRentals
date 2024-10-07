<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'property_name' => $this->properties->name,
            // 'images' => PropertyImageResource::collection($this->propertyImages),
            'night_rate' => $this->properties->night_rate,
            "headline" => $this->properties->headline,
            "description" => $this->properties->description,
            "bedrooms" => $this->properties->bedrooms,
            "bathrooms" => $this->properties->bathrooms,
            "city" => $this->properties->city,
            "country" => $this->properties->country,
            "address" => $this->properties->address,
            "nightRate" => $this->properties->night_rate,
            "sleeps" => $this->properties->sleeps,
            "property_type" => $this->properties->category->name,
            'images'=> PropertyImageResource::collection($this->properties->propertyImages),
        ];
    }
}
