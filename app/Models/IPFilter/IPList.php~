<?php

namespace App\Models\IPFilter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IPList extends Model
{
    protected $table = 'ip_lists';

    protected $fillable = [
        'ip',
        'filter_id',
    ];

    public $timestamps = false;

    public function filter(): BelongsTo
    {
        return $this->belongsTo(IPFilter::class, 'filter_id');
    }
}
