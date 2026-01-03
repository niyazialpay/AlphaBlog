<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class ProfilePrivacy extends Model {
    use ModelLogger;

    protected $fillable = [
        'show_name',
        'show_surname',
        'show_location',
        'show_education',
        'show_job_title',
        'show_skills',
        'show_about',
        'show_social_links',
        'user_id'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
