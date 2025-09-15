<?php

namespace App\Api\Presentation\Http\Controllers;

use App\Api\Application\Comment\Services\CreateComment;
use App\Api\Application\Comment\Services\DeleteComment;
use App\Api\Application\Comment\Services\UpdateComment;
use App\Api\Application\Comment\Services\VoteForComment;
use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\Post;
use App\Api\Presentation\Http\Resources\CommentResource;
use App\Api\Presentation\Http\Resources\CommentVoteResource;
use App\Api\Shared\Enums\VoteDirection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()->get();

        return CommentResource::collection($comments);
    }

    public function show(Post $post, Comment $comment)
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        return new CommentResource($comment);
    }


    public function store(Request $request, Post $post, CreateComment $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }

        $validated = $request->validate(['content' => 'required|string']);
        $comment = $service->execute($user, $post, $validated['content']);

        return new CommentResource($comment);
    }

    public function update(Request $request, Post $post, Comment $comment, UpdateComment $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }
        if (!$user->can('update', [$post, $comment])) {
            abort(403);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $validated = $request->validate(['content' => 'sometimes|required|string']);
        $comment = $service->execute($comment, $validated['content']);

        return new CommentResource($comment);
    }

    public function destroy(Request $request, Post $post, Comment $comment, DeleteComment $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }
        if (!$user->can('update', [$post, $comment])) {
            abort(403);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $service->execute($comment);

        return response()->json(null, 204);
    }

    public function vote(Request $request, Post $post, Comment $comment, string $direction, VoteForComment $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }

        $rating = $service->execute($user, $post, $comment, VoteDirection::from($direction));

        return response()->json(['rating' => $rating]);
    }

    public function voteStatus(Request $request, Post $post, Comment $comment)
    {
        if (!$user = $request->user()) {
            abort(401);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $existingVote = $comment->votes()->where('user_id', $user->id)->first();

        return $existingVote ? CommentVoteResource::make($existingVote) : ['data' => ['direction' => null]];
    }
}
