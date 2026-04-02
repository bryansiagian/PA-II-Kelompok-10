<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Rack extends Model {
    use Blameable;
    protected $fillable = ['storage_id', 'name', 'active', 'created_by', 'updated_by'];
    public function storage() { return $this->belongsTo(Storage::class); }
    public function drugs() { return $this->hasMany(Drug::class); }
}