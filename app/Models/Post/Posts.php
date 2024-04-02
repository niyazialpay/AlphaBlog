<?php

namespace App\Models\Post;

use App\Models\User;
use App\Traits\Searchable;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\BelongsToMany;
use MongoDB\Laravel\Relations\HasMany;
use niyazialpay\MediaLibrary\HasMedia;
use niyazialpay\MediaLibrary\InteractsWithMedia;
use niyazialpay\MediaLibrary\MediaCollections\Models\Media;

class Posts extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    /**
     * @var bool|mixed
     */
    protected $collection = 'posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'meta_description',
        'meta_keywords',
        'views',
        'user_id',
        'tags',
        'post_type',
        'is_published',
        'language',
        'href_lang',
    ];

    protected $attributes = [
        'views' => 0,
        'post_type' => 'post',
        'is_published' => false,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categories::class, PostCategory::class, 'post_id', 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comments::class, 'post_id', '_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(PostHistory::class, 'post_id', '_id')->orderBy('created_at', 'DESC');
    }

    public function commentCount()
    {
        return $this->hasManyThrough(Comments::class, Posts::class, 'post_id', '_id', '_id', '_id');
    }

    public function searchableAs(): string
    {
        return config('scout.prefix').'posts';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function scopeForLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        $array['user'] = $this->user?->name.' '.$this->user?->surname;
        $array['username'] = $this->user?->nickname;
        $array['categories'] = $this->categories->pluck('name')->toArray();
        $array['tags'] = $this->tags;
        $array['category_id'] = $this->category_id;

        return $array;
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
