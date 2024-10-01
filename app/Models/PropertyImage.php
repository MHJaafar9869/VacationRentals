<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = ['property_id', 'image_path'];
    use HasFactory;
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
