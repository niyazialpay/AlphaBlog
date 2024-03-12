<?php

namespace App\Models\Post;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\BelongsToMany;
use MongoDB\Laravel\Relations\HasMany;
use niyazialpay\MediaLibrary\HasMedia;
use niyazialpay\MediaLibrary\InteractsWithMedia;
use niyazialpay\MediaLibrary\MediaCollections\Models\Media;

class Categories extends Model implements HasMedia
{
    use Searchable;
    use InteractsWithMedia;

    protected $collection = 'categories';

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
        'parent_id' => null
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Posts::class, PostCategory::class, 'category_id', 'post_id', '_id', '_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id', '_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Categories::class, 'parent_id', '_id');
    }

    /*public function postsCount(): HasManyThrough
    {
        return $this->hasManyThrough(Posts::class, Categories::class, 'category_id', 'post_id', '_id', '_id');
    }*/

    public function PostCount(): Attribute
    {
        return Attribute::make(
            get: fn () => count($this->post_id)
        );
    }

    public function CategoryPosts(){
        return $this->hasManyThrough(Posts::class, Categories::class, 'category_id', 'post_id', '_id', '_id');
    }


    public function categoryMedia()
    {
        return $this->hasOne(Media::class, 'model_id', '_id')->where('collection_name', 'categories');
    }

    public function searchableAs(): string
    {
        return config('scout.prefix').'categories';
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
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
