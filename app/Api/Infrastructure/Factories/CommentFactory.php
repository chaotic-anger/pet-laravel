<?php

namespace App\Api\Infrastructure\Factories;

use App\Api\Domain\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Api\Domain\Models\Comment>
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
