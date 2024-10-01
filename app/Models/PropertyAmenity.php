<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAmenity extends Model
{
    protected $fillable = ['property_id', 'amenity'];
    use HasFactory;
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
