<?php

namespace App\Models\Post;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Categories extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;
    use ModelLogger;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_description',
        'meta_keywords',
        'parent_id',
        'language',
        'href_lang',
    ];

    protected $attributes = [
        'parent_id' => null,
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Posts::class, PostCategory::class, 'category_id', 'post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    public function CategoryPosts(): HasManyThrough
    {
        return $this->hasManyThrough(Posts::class, Categories::class, 'category_id', 'post_id');
    }

    public function categoryMedia(): HasOne
    {
        return $this->hasOne(Media::class, 'model_id')->where('collection_name', 'categories');
    }

    public function searchableAs(): string
    {
        return config('scout.prefix').'categories';
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'category_image' => $this->categoryMedia?->getUrl('cover'),
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('resized')
            ->width(1920)
            ->height(1080)
            //->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('cover')
            ->width(850)
            ->height(480)
            //->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('thumb')
            ->width(365)
            ->height(200)
            //->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
    }
}
