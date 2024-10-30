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
        $originalPrice = $this->night_rate;    

        // الحصول على التاريخ الحالي
        $today = now(); // يمكنك استخدام now() للحصول على التاريخ والوقت الحالي
        
        // تحويل تواريخ البدء والانتهاء إلى كائنات Carbon
        $offerStartDate = \Carbon\Carbon::parse($this->offer_start_date);
        $offerEndDate = \Carbon\Carbon::parse($this->offer_end_date);
        
        // تحقق مما إذا كان العرض ساريًا
        $isOfferActive = $today->between($offerStartDate, $offerEndDate);

        // حساب سعر العرض
        $offerPrice = $isOfferActive && $this->offer > 0 
            ? $originalPrice - ($originalPrice * ($this->offer / 100)) 
            : $originalPrice;
        return [
            "id" => $this->id,
            "name" => $this->name,
            "headline" => $this->headline,
            "description" => $this->description,
            "bedrooms" => $this->bedrooms,
            "bathrooms" => $this->bathrooms,
            "location" => $this->location,
            "night_rate" => $originalPrice,
            "total_price" => $offerPrice,       
            "offer" => $this->offer,
            "offer_start_date" => $this->offer_start_date,
            "offer_end_date" => $this->offer_end_date,
            "sleeps" => $this->sleeps,
            "status" => $this->status,
            "createdAt" => $this->created_at,
            "modifiedAt" => $this->updated_at,
            "category_id" => $this->category->id,
            "property_type" => $this->category->name,
            "owner_name" => $this->owner->name,
            "owner_email" => $this->owner->email,
            "owner_image" => $this->owner->image,
            "owner_id" => $this->owner->id,
            "owner_company_name" => $this->owner->company_name,
            "owner_phone" => $this->owner->phone,
            "longitude" => $this->longitude,
            "latitude" => $this->latitude,
            'images' => PropertyImageResource::collection($this->propertyImages),
            'amenities' => AmenityResource::collection($this->propertyAmenities),
            'bookings' => BookingResource::collection($this->booking),
        ];
    }
}