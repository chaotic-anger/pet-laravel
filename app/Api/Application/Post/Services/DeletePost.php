<?php

declare(strict_types=1);


namespace App\Api\Application\Post\Services;


use App\Api\Domain\Models\Post;

class DeletePost
{
    public function execute(Post $post): void
    {
        $post->delete();
    }
}
