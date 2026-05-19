<?php

namespace Database\Factories;

use App\Models\Post\Posts;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Posts>
 */
class PostsFactory extends Factory
{
    protected $model = Posts::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->randomNumber(4),
            'content' => fake()->paragraphs(3, true),
            'meta_description' => fake()->sentence(),
            'user_id' => User::factory(),
            'post_type' => 'post',
            'is_published' => false,
            'language' => 'tr',
            'views' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }
}
