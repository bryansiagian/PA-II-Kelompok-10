<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderItemSnapshot extends Model
{
    use HasUuids;

    protected $table = 'order_items_snapshot';

    protected $fillable = [
        'id',
        'order_id',
        'product_name',
        'product_id',
        'quantity',
        'price_at_order',
    ];

    protected $casts = [
        'price_at_order' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(OrderSnapshot::class, 'order_id', 'id');
    }
}
