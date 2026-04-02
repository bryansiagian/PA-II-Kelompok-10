<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model {
    use \App\Traits\Blameable;
    protected $fillable = ['code', 'name', 'active'];

    public function products() {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}


