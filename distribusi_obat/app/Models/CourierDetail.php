<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierDetail extends Model {
    protected $fillable = ['user_id', 'vehicle_type', 'vehicle_plate'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}