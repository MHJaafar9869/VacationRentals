<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $table = 'blocks';
    protected $fillable = ['start_date', 'end_date', 'property_id'];

    public function property()
    {
        $this->belongsTo(Property::class);
    }
}
