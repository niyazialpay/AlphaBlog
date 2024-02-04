<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class AdvertiseSettings extends Model
{
    protected $fillable = [
        'js',
        'header',
        'footer',
        'sidebar',
        'post'
    ];

    public $timestamps = false;
}
