<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\Blameable;

class Product extends Model {
    use HasFactory, Blameable;

    public $incrementing = false; // Karena menggunakan UUID
    protected $keyType = 'string';

    protected $fillable = [
        'product_code',
        'product_category_id',
        'warehouse_id',
        'rack_id',
        'sku',
        'name',
        'image',
        'price',
        'description',
        'unit',
        'stock',
        'min_stock',
        'active',
    ];

    protected static function booted() {
        static::creating(function ($product) {
            if (empty($product->id)) {
                $product->id = (string) Str::uuid();
            }
        });
    }

    public function category() {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function rack() {
        return $this->belongsTo(Rack::class);
    }

    public function stockLogs() {
        return $this->hasMany(StockLog::class, 'product_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
