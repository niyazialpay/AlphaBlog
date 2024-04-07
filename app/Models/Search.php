<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;

class Search extends Model
{
    protected $collection = 'search';

    protected $fillable = [
        'search',
        'language',
        'ip',
        'user_agent',
        'checked',
        'think'
    ];

    protected $attributes = [
        'checked' => false,
        'think' => false
    ];

    
}
