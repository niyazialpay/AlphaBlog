<?php

namespace App\Models\Settings;



use Illuminate\Database\Eloquent\Model;

class AdvertiseSettings extends Model
{
    protected $fillable = [
        'google_ad_manager',
        'square_display_advertise',
        'vertical_display_advertise',
        'horizontal_display_advertise',
    ];

    public $timestamps = false;
}
