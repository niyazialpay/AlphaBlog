<?php

namespace App\Models\Settings;


use MongoDB\Laravel\Eloquent\Model;

class GeneralSettings extends Model
{
    protected $fillable = [
        'logo',
        'favicon',
        'contact_email'
    ];

    public $timestamps = false;
}
