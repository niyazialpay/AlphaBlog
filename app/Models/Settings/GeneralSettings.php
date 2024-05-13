<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GeneralSettings extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'general_settings';

    protected $fillable = [
        'contact_email',
        'sharethis',
        'logo',
        'favicon',
    ];

    public $timestamps = false;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('r_57x57')
            ->width(57)
            ->height(57)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_60x60')
            ->width(60)
            ->height(60)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_72x72')
            ->width(72)
            ->height(72)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_76x76')
            ->width(76)
            ->height(76)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_114x114')
            ->width(114)
            ->height(114)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_120x120')
            ->width(120)
            ->height(120)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_144x144')
            ->width(144)
            ->height(144)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_152x152')
            ->width(152)
            ->height(152)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_180x180')
            ->width(180)
            ->height(180)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_192x192')
            ->width(192)
            ->height(192)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_32x32')
            ->width(32)
            ->height(32)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_96x96')
            ->width(96)
            ->height(96)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('r_16x16')
            ->width(16)
            ->height(16)
            ->nonOptimized()->keepOriginalImageFormat();
    }
}
