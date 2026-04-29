<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model {
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference'
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}