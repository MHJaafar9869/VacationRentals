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
        return [
            "id" => $this->id,
            "name" => $this->name,
            "headline" => $this->headline,
            "description" => $this->description,
            "bedrooms" => $this->bedrooms,
            "bathrooms" => $this->bathrooms,
            "city" => $this->city,
            "country" => $this->country,
            "address" => $this->address,
            "night_rate" => $this->night_rate,
            "sleeps" => $this->sleeps,
            "status" => $this->status,
            "createdAt" => $this->created_at,
            "modifiedAt" => $this->updated_at,
            "category_id" => $this->category->id,
            "property_type" => $this->category->name,
            "owner_name" => $this->owner->name,
            "owner_email" => $this->owner->email,
            "owner_id" => $this->owner->id,
            "owner_company_name" => $this->owner->company_name,
            "owner_phone" => $this->owner->phone,
            "longitude" => $this->longitude,
            "latitude" => $this->latitude,
            'images' => PropertyImageResource::collection($this->propertyImages),
            'amenities' => AmenityResource::collection($this->propertyAmenities),
        ];
    }
}
