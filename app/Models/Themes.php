<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Themes extends Model
{
    protected $fillable = [
        'name',
        'folder',
        'is_default',
    ];

    public $timestamps = false;
}
