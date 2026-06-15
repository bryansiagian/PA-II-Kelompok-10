<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderSnapshot extends Model
{
    use HasUuids;

    protected $table = 'orders_snapshot';

    protected $fillable = [
        'id',
        'user_id',
        'status_name',
        'payment_status',
        'payment_method',
        'total',
        'regency',
        'district',
        'village',
        'phone_order',
        'paid_at',
        'created_at',
        'updated_at',
        'synced_at',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'synced_at'  => 'datetime',
        'total'      => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItemSnapshot::class, 'order_id', 'id');
    }
}
