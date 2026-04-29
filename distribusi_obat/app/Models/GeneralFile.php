<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class GeneralFile extends Model {
    use Blameable;
    protected $fillable = ['name', 'file_path', 'active', 'created_by', 'updated_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}