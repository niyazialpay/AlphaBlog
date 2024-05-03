<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Themes extends Model
{
    protected $fillable = [
        'name',
        'folder',
        'is_default',
    ];

    public $timestamps = false;
}
