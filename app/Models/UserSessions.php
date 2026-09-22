<?php

namespace App\Models;

use hisorange\BrowserDetect\Parser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSessions extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'session_id',
        'country_code',
        'country_name',
        'region_code',
        'region_name',
        'city_name',
        'zip_code',
    ];

    protected $browser;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->browser = app(Parser::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }

    public function getBrowserNameAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->browserName();
    }

    public function getBrowserVersionAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->browserVersion();
    }

    public function getOperatingSystemAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->platformName();
    }
}
