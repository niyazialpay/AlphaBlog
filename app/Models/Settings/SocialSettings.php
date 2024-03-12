<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class SocialSettings extends Model
{
    protected $fillable = [
        'social_networks_header',
        'social_networks_footer'
    ];

    public $timestamps = false;
}
