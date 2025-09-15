<?php

declare(strict_types=1);


namespace App\Api\Application\Comment\Services;


use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\Post;
use App\Models\User;

class CreateComment
{
    public function execute(User $user, Post $post, string $content): Comment
    {
        return $post->comments()->create([
            'content' => $content,
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
