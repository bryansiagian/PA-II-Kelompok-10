<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Warehouse extends Model {
    use Blameable;
    protected $fillable = ['code', 'name', 'location', 'active'];
    public function products() { return $this->hasMany(Product::class); }
}
