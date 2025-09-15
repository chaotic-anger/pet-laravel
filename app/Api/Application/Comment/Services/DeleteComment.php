<?php

declare(strict_types=1);


namespace App\Api\Application\Comment\Services;


use App\Api\Domain\Models\Comment;

class DeleteComment
{
    public function execute(Comment $comment): void
    {
        $comment->delete();
    }
}
