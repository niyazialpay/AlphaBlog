<?php

namespace App\Models\Post;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Posts extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    /**
     * @var bool|mixed
     */
    protected $table = 'posts';

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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categories::class, PostCategory::class, 'post_id', 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comments::class, 'post_id', 'id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(PostHistory::class, 'post_id')->orderBy('created_at', 'DESC');
    }

    public function commentCount()
    {
        return $this->hasManyThrough(Comments::class, Posts::class, 'post_id', 'post_id', 'id', 'id');
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
        $array = [];
        $array['id'] = $this->id;
        $array['user'] = $this->user?->name.' '.$this->user?->surname;
        $array['username'] = $this->user?->nickname;
        $array['categories'] = $this->categories->pluck('name')->toArray();
        $array['title'] = $this->title;
        $array['content'] = $this->content;
        $array['created_at'] = $this->created_at;
        $array['language'] = $this->language;
        $array['post_type'] = $this->post_type;
        $array['category_id'] = $this->categories->pluck('id')->toArray();
        $array['is_published'] = $this->is_published;

        return $array;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('resized')
            ->width(1920)
            ->height(1080)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('cover')
            ->width(850)
            ->height(480)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('thumb')
            ->width(365)
            ->height(200)
            ->nonOptimized()->keepOriginalImageFormat();
    }
}
