<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductOrder extends Model {
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'product_order_status_id',
        'product_order_type_id', // Relasi ke Tipe/Kendaraan
        'product_order_delivery_id', // Relasi ke Metode Pengiriman
        'product_order_delivery_cost',
        'product_order_discount',
        'required_vehicle', // Jika masih ingin menyimpan string-nya (opsional)
        'notes',
        'total'
    ];

    protected static function booted() {
        static::creating(function ($order) {
            if (empty($order->id)) {
                $order->id = (string) Str::uuid();
            }
        });
    }

    /**
     * RELASI: Tipe Pesanan (Kendaraan)
     * Ini yang menyebabkan error tadi karena belum ada.
     */
    public function type() {
        return $this->belongsTo(ProductOrderType::class, 'product_order_type_id');
    }

    /**
     * RELASI: Metode Pengambilan (Kurir / Ambil Sendiri)
     */
    public function deliveryMethod() {
        return $this->belongsTo(ProductOrderDelivery::class, 'product_order_delivery_id');
    }

    /**
     * RELASI: Status Pesanan (Pending, Approved, dll)
     */
    public function status() {
        return $this->belongsTo(ProductOrderStatus::class, 'product_order_status_id');
    }

    public function items() {
        return $this->hasMany(ProductOrderDetail::class, 'product_order_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function delivery() {
        return $this->hasOne(Delivery::class, 'product_order_id');
    }
}