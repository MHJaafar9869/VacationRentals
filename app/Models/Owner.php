<?php

namespace App\Models;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Authenticatable implements MustVerifyEmail

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
        'provider_id',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

  
}