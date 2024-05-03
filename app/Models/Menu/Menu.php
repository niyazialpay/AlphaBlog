<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menu';

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
        return $this->hasMany(MenuItems::class, 'menu_id');
    }
}
