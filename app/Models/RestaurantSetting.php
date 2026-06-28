<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'description',
        'address',
        'phone',
        'opening_hours',
    ];
}
