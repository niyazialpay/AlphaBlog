<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected function casts(): array
    {
        return [
            'last_activity' => 'datetime',
        ];
    }
}
