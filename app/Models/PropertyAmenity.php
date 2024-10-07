<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAmenity extends Model
{
    use HasFactory;
    protected $fillable = ['property_id', 'amenity_id'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
