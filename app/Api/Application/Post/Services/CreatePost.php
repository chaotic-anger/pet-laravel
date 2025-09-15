<?php

declare(strict_types=1);


namespace App\Api\Application\Post\Services;


use App\Api\Domain\Models\Post;
use App\Models\User;

class CreatePost
{
    public function execute(User $user, string $title, string $content): Post
    {
        return Post::create([
            'title' => $title,
            'content' => $content,
            'user_id' => $user->id,
        ]);
    }
}
