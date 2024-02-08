<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class AdvertiseSettings extends Model
{
    protected $fillable = [
        'js',
        'header',
        'footer',
        'sidebar1',
        'sidebar2',
        'post'
    ];

    public $timestamps = false;
}
