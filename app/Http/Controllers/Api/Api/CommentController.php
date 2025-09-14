<?php

namespace App\Http\Controllers\Api\Api;

use App\Enums\VoteDirection;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
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

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $post->comments()->create($validated);

        return new CommentResource($comment);
    }


    public function show(Post $post, Comment $comment)
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        return new CommentResource($comment);
    }

    public function update(Request $request, Post $post, Comment $comment)
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => 'sometimes|required|string',
        ]);

        $comment->update($validated);

        return new CommentResource($comment);
    }

    public function destroy(Post $post, Comment $comment)
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $comment->delete();

        return response()->json(null, 204);
    }

    public function vote(Request $request, Post $post, Comment $comment, string $direction)
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $dir = VoteDirection::from($direction);
        $userId = $request->user()->id;

        $existingVote = $comment->votes()->where('user_id', $userId)->first();

        if (!$existingVote) {
            $comment->votes()->create(['user_id' => $userId, 'direction' => $dir,]);
        } else {
            if ($existingVote->direction === $dir) {
                return response()->json(['message' => 'Вы уже голосовали в этом направлении'], 400);
            }

            $existingVote->update([
                'direction' => $dir,
            ]);
        }

        $comment->refresh();

        return response()->json(['rating' => $comment->rating,]);
    }
}
