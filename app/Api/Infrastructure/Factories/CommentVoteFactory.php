<?php

namespace App\Api\Infrastructure\Factories;

use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\CommentVote;
use App\Api\Shared\Enums\VoteDirection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Api\Domain\Models\CommentVote>
 */
class CommentVoteFactory extends Factory
{
    protected $model = CommentVote::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'comment_id' => Comment::factory(),
            'direction' => $this->faker->randomElement([
                VoteDirection::UP,
                VoteDirection::DOWN,
            ]),
        ];
    }
}
