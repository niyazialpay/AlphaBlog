<?php

namespace App\Models\Settings;

use App\Models\Logs;
use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class SeoSettings extends Model
{
    use ModelLogger;

    protected $fillable = [
        'site_name',
        'title',
        'description',
        'keywords',
        'author',
        'robots',
        'language',
    ];

    public $timestamps = false;

}
