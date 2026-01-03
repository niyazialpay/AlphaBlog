<?php

namespace App\Models\IPFilter;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteList extends Model
{
    use ModelLogger;

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
