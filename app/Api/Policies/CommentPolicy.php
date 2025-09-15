<?php

namespace App\Api\Policies;

use App\Api\Models\Comment;
use App\Api\Models\Post;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Post $post, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Post $post, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
