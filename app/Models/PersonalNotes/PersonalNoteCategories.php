<?php

namespace App\Models\PersonalNotes;


use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class PersonalNoteCategories extends Model
{
    protected $collection = 'personal_note_categories';

    protected $fillable = [
        'name',
        'user_id'
    ];

    public $timestamps = false;

protected function casts(): array
    {
        return [
            'name' => 'encrypted'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PersonalNotes::class, 'category_id', '_id');
    }
}
