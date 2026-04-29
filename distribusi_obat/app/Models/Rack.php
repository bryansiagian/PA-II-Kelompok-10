<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan ini
use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Rack extends Model {
    use HasFactory, Blameable;

    // Sesuaikan fillable dengan field di migrasi/controller (warehouse_id)
    protected $fillable = [
        'warehouse_id',
        'name',
        'active',
        'created_by',
        'updated_by'
    ];

    /**
     * Relasi ke Warehouse (Gudang)
     * Kita ganti nama fungsi dari storage() ke warehouse() agar sinkron dengan tabel
     */
    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relasi ke Product (Satu rak berisi banyak produk)
     * Nama fungsi sebaiknya jamak (products) karena relasi HasMany
     */
    public function products() {
        return $this->hasMany(Product::class, 'rack_id');
    }
}
