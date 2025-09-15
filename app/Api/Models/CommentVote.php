<?php

namespace App\Api\Models;

use App\Api\Enums\VoteDirection;
use App\Api\Events\CommentVoteCreated;
use App\Api\Events\CommentVoteDeleted;
use App\Api\Events\CommentVoteUpdated;
use App\Api\Factory\CommentVoteFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
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

    protected static function newFactory(): CommentVoteFactory|Factory
    {
        return CommentVoteFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
