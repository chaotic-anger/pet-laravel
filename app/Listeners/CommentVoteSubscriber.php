<?php

namespace App\Listeners;

use App\Enums\VoteDirection;
use App\Events\CommentVoteCreated;
use App\Events\CommentVoteDeleted;
use App\Events\CommentVoteUpdated;
use App\Models\CommentVote;

class CommentVoteSubscriber
{
    public function subscribe($events): void
    {
        $events->listen(CommentVoteCreated::class, [self::class, 'mutateCommentRatingByNewVote']);
        $events->listen(CommentVoteUpdated::class, [self::class, 'changeCommentRatingByVoteChange']);
        $events->listen(CommentVoteDeleted::class, [self::class, 'changeCommentRatingByVoteRemove']);
    }

    public function mutateCommentRatingByNewVote(CommentVoteCreated $event): void
    {
        $vote = $event->commentVote;
        $this->applyRating($vote);
        $vote->comment->save();
    }

    public function changeCommentRatingByVoteChange(CommentVoteUpdated $event): void
    {
        $vote = $event->commentVote;
        $original = VoteDirection::from($vote->getOriginal('direction'));
        $this->rollbackRating($original, $vote);
        $this->applyRating($vote);
        $vote->comment->save();
    }

    public function changeCommentRatingByVoteRemove(CommentVoteDeleted $event): void
    {
        $vote = $event->commentVote;
        $this->rollbackRating($vote->direction, $vote);
        $vote->comment->save();
    }

    private function applyRating(CommentVote $vote): void
    {
        $vote->comment->rating += match ($vote->direction) {
            VoteDirection::UP => 1,
            VoteDirection::DOWN => -1,
        };
    }

    private function rollbackRating(VoteDirection $original, CommentVote $vote): void
    {
        $vote->comment->rating += match ($original) {
            VoteDirection::UP => -1,
            VoteDirection::DOWN => 1,
        };
    }
}
