<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable; 

class Gallery extends Model {
    use Blameable;
    protected $fillable = ['title', 'active', 'created_by', 'updated_by'];
    public function files() {
        return $this->hasMany(GalleryFile::class, 'gallery_id')->where('active', 1);
    }
}