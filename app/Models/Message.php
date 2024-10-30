<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $fillable = [
        'owner_id',
        'guest_id',
        'booking_id',
        'message'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}