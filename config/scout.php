<?php

return [

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'after_commit' => false,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'posts' => [
                'filterableAttributes' => [
                    'user_id',
                    'category_id',
                    'post_type',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'is_published',
                    'language',
                    'post_type',
                    'created_at',
                    'posts.created_at',
                    'meta_description',
                    'meta_keywords',
                ],
                'searchableAttributes' => [
                    'title',
                    'content',
                    'username',
                    'user',
                    'categories',
                    'tags',
                    'meta_description',
                    'meta_keywords',
                    'created_at',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'user_id',
                    'categories',
                    'title',
                ],
                'displayedAttributes' => [
                    '*',
                ],
            ],
            'categories' => [
                'filterableAttributes' => [
                    'parent_id',
                    'language',
                ],
                'searchableAttributes' => [
                    'name',
                    'description',
                    'meta_description',
                    'meta_keywords',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'name',
                ],
                'displayedAttributes' => [
                    '*',
                ],
            ],
            'personal_notes' => [
                'filterableAttributes' => [
                    'user_id',
                    'category_id',
                ],
                'searchableAttributes' => [
                    'title',
                    'content',
                    'category_id',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'user_id',
                    'category_id',
                    'title',
                ],
            ],
            'users' => [
                'filterableAttributes' => [
                    'name',
                    'surname',
                    'username',
                    'nickname',
                    'email',
                ],
                'searchableAttributes' => [
                    'name',
                    'surname',
                    'username',
                    'nickname',
                    'email',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'id',
                    'name',
                    'surname',
                    'username',
                    'nickname',
                    'email',
                ],
                'displayedAttributes' => [
                    '*',
                ],
            ],
        ],
    ],

];
