<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'type',
        'subtype',
        'brand',
        'plate_number',
        'color',
        'active',
    ];

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
