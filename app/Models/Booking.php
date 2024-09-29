<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['start_date', 'end_date'];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
    public function property()
    {
        return $this->belongsTo(Property::class, "property_id", "id");
    }
}