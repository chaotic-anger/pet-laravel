<?php

namespace App\Models;

use App\Enums\VoteDirection;
use App\Events\CommentVoteCreated;
use App\Events\CommentVoteDeleted;
use App\Events\CommentVoteUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class CommentVote extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'comment_id',
        'direction',
    ];

    protected $casts = [
        'direction' => VoteDirection::class,
    ];

    /**
     * Карта событий для модели.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'created' => CommentVoteCreated::class,
        'updated' => CommentVoteUpdated::class,
        'deleted' => CommentVoteDeleted::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
