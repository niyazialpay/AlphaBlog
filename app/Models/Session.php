<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use ModelLogger;

    protected function casts(): array
    {
        return [
            'last_activity' => 'datetime',
        ];
    }
}
