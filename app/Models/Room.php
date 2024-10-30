<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'host_id',
        'property_id',
        'channel_name',
    ];

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id', 'id');
    }

    public function host()
    {
        return $this->belongsTo(Owner::class, 'host_id', 'id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'booking_id', 'booking_id');
    }
}