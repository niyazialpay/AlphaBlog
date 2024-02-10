<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class SocialSettings extends Model
{
    protected $fillable = [
        'social_networks'
    ];

    public $timestamps = false;
}
