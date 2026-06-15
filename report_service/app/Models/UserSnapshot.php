<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSnapshot extends Model
{
    protected $table = 'users_snapshot';

    // ID integer bukan UUID — tidak pakai HasUuids
    public $incrementing = false;
    protected $keyType   = 'int';

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'status',
        'active',
        'regency',
        'district',
        'village',
        'email_verified_at',
        'created_at',
        'updated_at',
        'synced_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'synced_at'         => 'datetime',
    ];
}
