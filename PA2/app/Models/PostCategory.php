<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class PostCategory extends Model {
    use Blameable;
    protected $fillable = ['name', 'active', 'created_by', 'updated_by'];

    public function posts() {
        // Pastikan nama foreign key sesuai dengan migration (post_category_id)
        return $this->hasMany(Post::class, 'post_category_id');
    }
}