<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model {
    // Pastikan nama tabel benar jika tidak mengikuti jamak bahasa inggris
    protected $table = 'shipment_trackings';

    protected $fillable = [
        'delivery_id',
        'location',
        'description'
    ];
}