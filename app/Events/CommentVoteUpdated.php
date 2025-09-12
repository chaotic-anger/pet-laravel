<?php

namespace App\Events;

use App\Models\CommentVote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentVoteUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public CommentVote $commentVote)
    {
    }
}
