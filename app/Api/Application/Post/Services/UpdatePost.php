<?php

declare(strict_types=1);


namespace App\Api\Application\Post\Services;


use App\Api\Domain\Models\Post;

class UpdatePost
{
    public function execute(Post $post, string $title, string $content): Post
    {
        $post->update(['title' => $title, 'content' => $content]);

        return $post;
    }
}
