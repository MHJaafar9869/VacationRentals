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
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function host()
    {
        return $this->belongsTo(Owner::class, 'host_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}