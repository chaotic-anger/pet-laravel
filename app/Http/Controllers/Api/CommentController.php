<?php

namespace App\Http\Controllers\Api;

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

    public function vote(Post $post, Comment $comment, string $direction)
    {
        $dir = VoteDirection::from($direction);

        $comment->rating += match ($dir) {
            VoteDirection::UP => 1,
            VoteDirection::DOWN => -1,
        };
        $comment->save();

        return response()->json(['rating' => $comment->rating]);
    }
}
