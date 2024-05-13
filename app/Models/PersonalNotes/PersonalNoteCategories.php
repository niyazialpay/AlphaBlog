<?php

namespace App\Models\PersonalNotes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalNoteCategories extends Model
{
    protected $table = 'personal_note_categories';

    protected $fillable = [
        'name',
        'user_id',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PersonalNotes::class, 'category_id');
    }
}
