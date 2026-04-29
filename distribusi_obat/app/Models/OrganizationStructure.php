<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrganizationStructure extends Model {
    protected $fillable = [
        'name',
        'position',
        'photo',
        'order'
    ];
}