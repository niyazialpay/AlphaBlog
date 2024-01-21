<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;

    protected $collection = 'post_categories';

    protected $fillable = [
        'post_id',
        'category_id',
        'is_primary'
    ];

    protected $attributes = [
        'is_primary' => false
    ];

    public function post()
    {
        return $this->belongsTo(Posts::class, 'post_id', '_id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', '_id');
    }

}
