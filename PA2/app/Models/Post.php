<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\Blameable;

class Post extends Model {
    use Blameable;
    protected $fillable = [
        'user_id',
        'post_category_id',
        'title',
        'slug',
        'content',
        'image',
        'status',
        'active',
        'created_by',
        'updated_by'
    ];

    public function category() {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }
}