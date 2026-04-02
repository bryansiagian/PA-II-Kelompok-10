<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductOrderDetail extends Model {
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_order_id',
        'product_id',
        'quantity',
        'price_at_order'
    ];

    protected static function booted() {
        static::creating(function ($detail) {
            $detail->id = (string) Str::uuid();
        });
    }

    public function order() {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}