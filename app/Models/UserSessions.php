<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use hisorange\BrowserDetect\Parser;

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
        'zip_code'
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

    // Browser name attribute
    public function getBrowserNameAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->browserName();
    }

    // Browser version attribute
    public function getBrowserVersionAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->browserVersion();
    }

    // Operating system attribute
    public function getOperatingSystemAttribute(): string
    {
        return $this->browser->parse($this->user_agent)->platformName();
    }
}
