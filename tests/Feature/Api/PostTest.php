<?php

namespace Tests\Feature\Api;


use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\Post;
use App\Api\Presentation\Http\Controllers\PostController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(PostController::class)]
class PostTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_check_correct_structure()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->getJson('/api/posts');
        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'content', 'created_at']
                ],
                'links',
                'meta'
            ]);

        $data = $response->json('data');

        $this->assertCount(1, $data);


        $this->assertEquals($post->id, $data[0]['id']);
        $this->assertEquals($post->title, $data[0]['title']);
        $this->assertEquals($post->content, $data[0]['content']);
        $this->assertNotEmpty($data[0]['created_at']);
    }

    #[Test]
    public function index_returns_paginated_posts()
    {
        $author = User::factory()->create();
        Post::factory()->count(15)->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->getJson('/api/posts');

        $response
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->assertCount(10, $response->json('data'));
    }

    #[Test]
    public function index_returns_paginated_posts_second_page()
    {
        $author = User::factory()->create();
        $posts = Post::factory()->count(15)->for($author)->create();

        $firstPageResponse = $this
            ->actingAs($author)
            ->getJson('/api/posts?page=1');
        $firstPageResponse
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total']
            ]);

        $this->assertCount(10, $firstPageResponse->json('data'));

        $secondPageResponse = $this->getJson('/api/posts?page=2');
        $secondPageResponse
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total']
            ]);

        $this->assertCount(5, $secondPageResponse->json('data'));
        $firstPostSecondPage = $secondPageResponse->json('data')[0];
        $this->assertEquals($posts[10]->id, $firstPostSecondPage['id']);
    }

    #[Test]
    public function show_check_correct_structure()
    {
        $author = User::factory()->create();
        $commentators = User::factory()->count(3)->create()->shuffle();
        $post = Post::factory()->for($author)->create();
        foreach ($commentators as $commentator) {
            Comment::factory()->for($commentator)->for($post)->create();
        }

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'content',
                    'created_at',
                    'comments' => ['*' => ['id', 'content', 'rating', 'created_at', 'user' => ['id', 'name', 'email']]],
                    'user' => ['id', 'name', 'email']
                ]
            ])
            ->assertJsonCount(3, 'data.comments');
    }

    #[Test]
    public function show_check_correct_structure_wo_comments()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id");
        $response
            ->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'content',
                    'created_at',
                    'comments' => ['*' => ['id', 'content', 'rating', 'created_at', 'user' => ['id', 'name', 'email']]],
                    'user' => ['id', 'name', 'email']
                ]
            ])
            ->assertJsonCount(0, 'data.comments');
    }

    #[Test]
    public function show_returns_404_for_nonexistent_post()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/posts/999')->assertNotFound();
    }

    #[Test]
    public function store_creates_post_for_author()
    {
        $author = User::factory()->create();
        $data = ['title' => 'New Post', 'content' => 'Post content'];

        $response = $this->actingAs($author)->postJson('/api/posts', $data);

        $response->assertCreated()->assertJsonPath('data.title', 'New Post');
        $this->assertDatabaseHas('posts', array_merge($data, ['user_id' => $author->id]));
    }

    #[Test]
    public function store_fails_with_invalid_data()
    {
        $author = User::factory()->create();

        $response = $this->actingAs($author)->postJson('/api/posts');
        $response->assertUnprocessable()->assertJsonValidationErrors(['title', 'content']);

        $data = ['title' => '', 'content' => ''];
        $response = $this->actingAs($author)->postJson('/api/posts', $data);
        $response->assertUnprocessable()->assertJsonValidationErrors(['title', 'content']);

        $data = ['title' => 'Title', 'content' => ''];
        $response = $this->actingAs($author)->postJson('/api/posts', $data);
        $response->assertUnprocessable()->assertJsonValidationErrors(['content']);

        $data = ['title' => '', 'content' => 'Content'];
        $response = $this->actingAs($author)->postJson('/api/posts', $data);
        $response->assertUnprocessable()->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function store_requires_authentication()
    {
        $response = $this->postJson('/api/posts', ['title' => 'New Post', 'content' => 'Post content']);
        $response->assertUnauthorized();
    }

    #[Test]
    public function update_succeeds_for_author()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this->actingAs($author)->putJson("/api/posts/$post->id", [
            'title' => 'Updated',
            'content' => 'Updated text'
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated']);
    }

    #[Test]
    public function update_requires_authentication()
    {
        $post = Post::factory()->for(User::factory()->create())->create();
        $response = $this->putJson("/api/posts/$post->id", ['title' => 'New Post', 'content' => 'Post content']);
        $response->assertUnauthorized();
    }

    #[Test]
    public function update_forbidden_for_non_author()
    {
        $author = User::factory()->create();
        $anotherAuthor = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this
            ->actingAs($anotherAuthor)
            ->putJson("/api/posts/$post->id", ['title' => 'Hack'])
            ->assertForbidden();
    }

    #[Test]
    public function destroy_succeeds_for_owner()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this
            ->actingAs($user)
            ->deleteJson("/api/posts/$post->id")
            ->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function destroy_forbidden_for_non_owner()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this
            ->actingAs($other)
            ->deleteJson("/api/posts/$post->id")
            ->assertForbidden();
    }
}
