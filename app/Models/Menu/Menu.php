<?php

namespace App\Models\Menu;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Menu extends Model
{
    protected $collection = 'menu';

    protected $fillable = [
        'title',
        'menu_position',
        'language',
    ];

    protected $casts = [
        'title' => 'string',
        'menu_position' => 'string',
        'language' => 'string',
    ];

    public $timestamps = false;

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItems::class, 'menu_id', '_id');
    }
}
