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
        "sleeps",
        "night_rate",
        "status",
        "category_id",
        'latitude',
        'longitude',
        'owner_id',
        'offer',
        'offer_start_date',
        'offer_end_date',
        
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, "owner_id", "id");
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
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
