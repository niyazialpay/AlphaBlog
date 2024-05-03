<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
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
