<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use ModelLogger;

    protected $table = 'search';

    protected $fillable = [
        'search',
        'language',
        'ip',
        'user_agent',
        'checked',
        'think',
    ];

    protected $attributes = [
        'checked' => false,
        'think' => false,
    ];
}
