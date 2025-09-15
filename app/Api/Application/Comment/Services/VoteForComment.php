<?php

declare(strict_types=1);


namespace App\Api\Application\Comment\Services;


use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\Post;
use App\Api\Shared\Enums\VoteDirection;
use App\Models\User;

class VoteForComment
{
    public function execute(User $user, Post $post, Comment $comment, VoteDirection $direction): int
    {
        if ($comment->post_id !== $post->id) {
            throw new \RuntimeException('Comment does not belong to post', 404);
        }

        $existingVote = $comment->votes()->where('user_id', $user->id)->first();

        if (!$existingVote) {
            $comment->votes()->create([
                'user_id' => $user->id,
                'direction' => $direction,
            ]);
        } else {
            if ($existingVote->direction === $direction) {
                throw new \InvalidArgumentException("Can't vote twice", 400);
            }

            $existingVote->update([
                'direction' => $direction,
            ]);
        }

        $comment->refresh();

        return $comment->rating;
    }
}
