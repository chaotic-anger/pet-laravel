<?php

namespace Tests\Feature\Api;


use App\Http\Controllers\Api\PostController;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(PostController::class)]
class PostTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function postIndexSuccess()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'content', 'created_at']
                ],
                'links',
                'meta'
            ]);
    }

    #[Test]
    public function postShowSuccess()
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                ]
            ]);
    }

    #[Test]
    public function postShowNotFound()
    {
        $response = $this->getJson('/api/posts/999');

        $response->assertStatus(404)
            ->assertJson([
                'title' => 'Not Found',
                'status' => 404,
            ]);
    }

    #[Test]
    public function postCreateSuccess()
    {
        $data = [
            'title' => 'New Post',
            'content' => 'Post content'
        ];

        $response = $this->postJson('/api/posts', $data);

        $response->assertCreated()
            ->assertJson([
                'data' => $data
            ]);

        $this->assertDatabaseHas('posts', $data);
    }

    #[Test]
    public function postCreateEmptyBodyError()
    {
        $response = $this->postJson('/api/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    #[Test]
    public function postUpdateSuccess()
    {
        $post = Post::factory()->create();

        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $data);

        $response->assertOk()
            ->assertJson([
                'data' => $data
            ]);

        $this->assertDatabaseHas('posts', array_merge(['id' => $post->id], $data));
    }

    #[Test]
    public function postDeleteSuccess()
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
