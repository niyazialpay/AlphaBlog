<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessages extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'language',
        'ip_address',
        'user_agent',
    ];
}
