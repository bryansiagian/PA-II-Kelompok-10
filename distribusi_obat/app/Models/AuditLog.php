<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AuditLog extends Model {
    protected $fillable = [
        'user_id',
        'action'
    ];

    public function user(): BelongsTo
    {
        // AuditLog milik seorang User
        return $this->belongsTo(User::class, 'user_id');
    }
}
