<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Authenticatable

{
    
    use HasFactory , HasApiTokens , Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'gender',
        'image',
        'role',
        'description',
        'company_name',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}