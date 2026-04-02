<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Contact extends Model {
    use Blameable;
    protected $fillable = ['key', 'title', 'value', 'image', 'active', 'created_by', 'updated_by'];
}
