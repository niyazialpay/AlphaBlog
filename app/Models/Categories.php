<?php

namespace App\Models;

use App\Traits\Searchable;
use MongoDB\Laravel\Eloquent\Model;

class Categories extends Model
{
    use Searchable;

    protected $collection = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'meta_description',
        'meta_keywords',
        'parent_id',
        'language',
        'href_lang',
    ];

    protected $attributes = [
        'parent_id' => null
    ];

    public function posts(): \MongoDB\Laravel\Relations\BelongsToMany
    {
        return $this->belongsToMany(Posts::class, 'post_categories', 'category_id', 'post_id');
    }

    public function parent(): \MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id', '_id');
    }

    public function children(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(Categories::class, 'parent_id', '_id');
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
}
