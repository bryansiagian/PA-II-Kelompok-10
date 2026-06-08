<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductSnapshot extends Model
{
    use HasUuids;

    protected $table = 'products_snapshot';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'product_code',
        'name',
        'category_name',
        'price',
        'unit',
        'stock',
        'min_stock',
        'active',
        'created_at',
        'updated_at',
        'synced_at',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'synced_at' => 'datetime',
    ];
}
