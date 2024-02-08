<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;
use niyazialpay\MediaLibrary\HasMedia;
use niyazialpay\MediaLibrary\InteractsWithMedia;

class GeneralSettings extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'contact_email'
    ];

    public $timestamps = false;
}
