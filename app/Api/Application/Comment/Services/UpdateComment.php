<?php

declare(strict_types=1);


namespace App\Api\Application\Comment\Services;


use App\Api\Domain\Models\Comment;

class UpdateComment
{
    public function execute(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);

        return $comment;
    }
}
