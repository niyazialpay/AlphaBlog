<?php

namespace App\Models\Settings;

use App\Models\Logs;
use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class SocialSettings extends Model
{
    use ModelLogger;

    protected $fillable = [
        'social_networks_header',
        'social_networks_footer',
    ];

    public $timestamps = false;
}
