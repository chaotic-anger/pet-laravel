<?php

namespace App\Http\Controllers\Api\Api;

use App\Enums\VoteDirection;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentVoteResource;
use App\Models\Comment;
use App\Models\Post;
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


    public function store(Request $request, Post $post)
    {
        if (!$request->user()) {
            abort(401);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        return new CommentResource($comment);
    }

    public function update(Request $request, Post $post, Comment $comment)
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

        $validated = $request->validate([
            'content' => 'sometimes|required|string',
        ]);

        $comment->update($validated);

        return new CommentResource($comment);
    }

    public function destroy(Request $request, Post $post, Comment $comment)
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

        $comment->delete();

        return response()->json(null, 204);
    }

    public function vote(Request $request, Post $post, Comment $comment, string $direction)
    {
        if (!$request->user()) {
            abort(401);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $voteDirection = VoteDirection::from($direction);
        $userId = $request->user()->id;

        $existingVote = $comment->votes()->where('user_id', $userId)->first();

        if (!$existingVote) {
            $comment->votes()->create(['user_id' => $userId, 'direction' => $voteDirection]);
        } else {
            if ($existingVote->direction === $voteDirection) {
                return response()->json(['message' => "Can't vote twice"], 400);
            }

            $existingVote->update([
                'direction' => $voteDirection,
            ]);
        }

        $comment->refresh();

        return response()->json(['rating' => $comment->rating]);
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

        return $existingVote ? CommentVoteResource::make($existingVote) : ['direction' => null];
    }
}
