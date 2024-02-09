<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class AdvertiseSettings extends Model
{
    protected $fillable = [
        'google_ad_manager'
    ];
}
