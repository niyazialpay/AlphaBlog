<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItems extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'title',
        'url',
        'target',
        'icon',
        'parent_id',
        'language',
        'menu_type',
        'menu_id',
    ];

    protected $casts = [
        'title' => 'string',
        'url' => 'string',
        'target' => 'string',
        'icon' => 'string',
        'order' => 'integer',
        'parent_id' => 'string',
        'language' => 'string',
        'menu_position' => 'string',
        'menu_type' => 'string',
    ];

    public $timestamps = false;

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItems::class, 'parent_id');
    }
}
