<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class SeoSettings extends Model
{
    protected $fillable = [
        'title',
        'description',
        'keywords',
        'author',
        'robots',
        'language'
    ];

    public $timestamps = false;
}
