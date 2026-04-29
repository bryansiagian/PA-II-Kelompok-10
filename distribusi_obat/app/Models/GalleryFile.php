<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class GalleryFile extends Model {
    use Blameable;
    protected $fillable = ['gallery_id', 'file_path', 'file_type', 'active', 'created_by', 'updated_by'];

    public function gallery() {
        return $this->belongsTo(Gallery::class);
    }
}