<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
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
