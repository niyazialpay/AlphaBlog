<?php

namespace App\Models\Menu;


use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class MenuItems extends Model
{
    protected $collection = 'menu_items';

    protected $fillable = [
        'title',
        'url',
        'target',
        'icon',
        'parent_id',
        'language',
        'menu_type',
        'menu_id'
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
        return $this->belongsTo(Menu::class, 'menu_id', '_id');
    }
}
