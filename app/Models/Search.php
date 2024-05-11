<?php

namespace App\Models;




use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $table = 'search';

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
