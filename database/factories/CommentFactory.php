<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 不显式指定时自动创建所属文章和作者；
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
