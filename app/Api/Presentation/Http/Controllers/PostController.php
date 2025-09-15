<?php

namespace App\Api\Presentation\Http\Controllers;

use App\Api\Application\Post\Services\CreatePost;
use App\Api\Application\Post\Services\DeletePost;
use App\Api\Application\Post\Services\UpdatePost;
use App\Api\Domain\Models\Post;
use App\Api\Presentation\Http\Resources\PostResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(Post::paginate(10));
    }

    public function store(Request $request, CreatePost $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $post = $service->execute($user, $validated['title'], $validated['content']);

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        $post->load('comments');

        return new PostResource($post);
    }

    public function update(Request $request, Post $post, UpdatePost $service)
    {
        if (!$user = $request->user()) {
            abort(401);
        }
        if (!$user->can('update', $post)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $service->execute($post, $validated['title'], $validated['content']);

        return new PostResource($post);
    }

    public function destroy(Request $request, Post $post, DeletePost $service)
    {
        if (!$request->user()?->can('delete', $post)) {
            abort(403);
        }

        $service->execute($post);

        return response()->json(null, 204);
    }
}
