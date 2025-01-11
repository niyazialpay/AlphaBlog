<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use ModelLogger;

    protected $table = 'contact_pages';

    protected $fillable = [
        'description',
        'meta_description',
        'meta_keywords',
        'maps',
        'language',
        'title',
        'slug',
    ];

    public $timestamps = false;
}
