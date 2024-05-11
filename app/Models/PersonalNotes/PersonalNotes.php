<?php

namespace App\Models\PersonalNotes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class PersonalNotes extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;

    protected $table = 'personal_notes';

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PersonalNoteCategories::class, 'category_id');
    }

    public function searchableAs(): string
    {
        return config('scout.prefix').'personal_notes';
    }

    public function toSearchableArray(): array
    {
        $array['user_id'] = $this->user->id;
        $array['title'] = $this->title;
        $array['created_at'] = $this->created_at;
        $array['updated_at'] = $this->updated_at;
        $array['category_id'] = $this->category_id;

        return $array;
    }

    public function registerMediaConversions(Media|null $media = null): void
    {
        $this->addMediaConversion('resized')
            ->width(1920)
            ->height(1080)
            ->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('cover')
            ->width(850)
            ->height(480)
            ->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
        $this->addMediaConversion('thumb')
            ->width(365)
            ->height(200)
            ->sharpen(10)
            ->nonOptimized()->keepOriginalImageFormat();
    }
}
