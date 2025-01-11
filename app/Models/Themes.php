<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class Themes extends Model
{
    use ModelLogger;

    protected $fillable = [
        'name',
        'folder',
        'is_default',
    ];

    public $timestamps = false;
}
