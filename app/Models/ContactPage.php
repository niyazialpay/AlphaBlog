<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ContactPage extends Model
{
    protected $fillable = [
        'description',
        'meta_description',
        'meta_keywords',
        'maps',
        'language',
    ];
}
