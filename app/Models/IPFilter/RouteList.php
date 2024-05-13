<?php

namespace App\Models\IPFilter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteList extends Model
{
    protected $table = 'route_lists';

    protected $fillable = [
        'route',
        'filter_id',
    ];

    public $timestamps = false;

    public function filter(): BelongsTo
    {
        return $this->belongsTo(IPFilter::class, 'filter_id');
    }
}
