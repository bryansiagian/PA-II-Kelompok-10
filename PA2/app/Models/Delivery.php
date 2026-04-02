<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model {
    protected $fillable = [
        'product_order_id',
        'courier_id',
        'tracking_number',
        'delivery_status_id',
        'image',
        'receiver_name',
        'receiver_relation',
        'delivery_note',
        'delivered_at'
    ];

    public function trackings()
    {
        return $this->hasMany(ShipmentTracking::class, 'delivery_id');
    }

    public function order() {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function status() {
        return $this->belongsTo(DeliveryStatus::class, 'delivery_status_id');
    }

    public function courier() {
        return $this->belongsTo(User::class, 'courier_id');
    }
}