<?php

namespace App\Api\Factory;

use App\Api\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Api\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => null,
            'user_id' => null,
            'content' => $this->faker->sentence(),
            'rating' => 0,
        ];
    }
}
