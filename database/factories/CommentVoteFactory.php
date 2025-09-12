<?php

namespace Database\Factories;

use App\Enums\VoteDirection;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommentVote>
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
