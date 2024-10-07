<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    protected $table = "properties";
    protected $fillable = [
        "name",
        "headline",
        "description",
        "bedrooms",
        "bathrooms",
        "location",
        "night_rate",
        "status",
        "category_id",
        'latitude',
        'longitude',
        'owner_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, "owner_id", "id");
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
    public function propertyImages()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function propertyAmenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities', 'property_id', 'amenity_id');
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
