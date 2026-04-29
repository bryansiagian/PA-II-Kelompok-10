<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOrderStatus extends Model {
    protected $table = 'product_order_status';
    protected $fillable = ['name', 'active'];
}
