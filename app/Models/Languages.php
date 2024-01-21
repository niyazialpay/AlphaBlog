<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;

class Languages extends Model
{
    protected $collection = 'languages';

    protected $fillable = [
        'name',
        'code',
        'flag',
        'is_active',
        'is_default'
    ];
}
