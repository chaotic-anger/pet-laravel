<?php

declare(strict_types=1);


namespace Feature\Api;

use App\Api\Enums\VoteDirection;
use App\Api\Http\Controllers\CommentController;
use App\Api\Models\Comment;
use App\Api\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(CommentController::class)]
class CommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_returns_post_comments()
    {
        $author = User::factory()->create();
        $commentators = User::factory()->count(3)->create()->shuffle();
        $post = Post::factory()->for($author)->create();
        foreach ($commentators as $commentator) {
            Comment::factory()->for($post)->for($commentator)->create();
        }

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id/comments");

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    #[Test]
    public function show_returns_single_comment()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($commentator)->create();

        $response = $this->actingAs($author)->getJson("/api/posts/$post->id/comments/$comment->id");

        $response->assertOk()->assertJsonPath('data.id', $comment->id);
    }

    #[Test]
    public function show_returns_404_if_comment_not_belongs_to_post()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $postWithComment = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($postWithComment)->for($commentator)->create();

        $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id/comments/$comment->id")
            ->assertNotFound();
    }

    #[Test]
    public function store_creates_comment_for_authenticated_author()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->postJson("/api/posts/$post->id/comments", ['content' => 'Test comment']);
        $response
            ->assertCreated()
            ->assertJsonPath('data.content', 'Test comment');

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'content' => 'Test comment']);
    }

    #[Test]
    public function store_creates_comment_for_another_authenticated_user()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this
            ->actingAs($commentator)
            ->postJson("/api/posts/$post->id/comments", ['content' => 'Test comment']);
        $response
            ->assertCreated()
            ->assertJsonPath('data.content', 'Test comment');

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'content' => 'Test comment']);
    }

    #[Test]
    public function store_fails_without_authentication()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $this->postJson("/api/posts/$post->id/comments", ['content' => 'X'])
            ->assertUnauthorized();
    }

    #[Test]
    public function update_succeeds_for_author()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->putJson("/api/posts/$post->id/comments/$comment->id", ['content' => 'Updated']);

        $response->assertOk()->assertJsonPath('data.content', 'Updated');
    }

    #[Test]
    public function update_forbidden_for_non_author()
    {
        $author = User::factory()->create();
        $wrongCommentator = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $this
            ->actingAs($wrongCommentator)
            ->putJson("/api/posts/$post->id/comments/$comment->id", [
                'content' => 'Updated'
            ])->assertForbidden();
    }

    #[Test]
    public function destroy_succeeds_for_author()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $this
            ->actingAs($author)
            ->deleteJson("/api/posts/$post->id/comments/$comment->id")
            ->assertNoContent();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function destroy_forbidden_for_non_owner()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $this
            ->actingAs($commentator)
            ->deleteJson("/api/posts/$post->id/comments/$comment->id")
            ->assertForbidden();
    }

    #[Test]
    public function vote_creates_new_vote()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $voter = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($commentator)->create();

        $like = VoteDirection::UP;
        $response = $this
            ->actingAs($voter)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/$like->value");
        $response
            ->assertOk()
            ->assertJsonStructure(['rating']);

        $this->assertDatabaseHas(
            'comment_votes',
            ['user_id' => $voter->id, 'comment_id' => $comment->id, 'direction' => $like->value]
        );
    }

    #[Test]
    public function vote_changes_direction_if_different()
    {
        $author = User::factory()->create();
        $commentator = User::factory()->create();
        $voter = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($commentator)->create();

        $this
            ->actingAs($voter)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/up");
        $this
            ->actingAs($voter)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/down")
            ->assertOk();

        $this->assertDatabaseHas('comment_votes', ['user_id' => $voter->id, 'direction' => VoteDirection::DOWN->value]);
    }

    #[Test]
    public function vote_fails_if_same_direction_twice()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $this
            ->actingAs($author)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/up");
        $this
            ->actingAs($author)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/up")
            ->assertStatus(400);
    }

    #[Test]
    public function vote_status_structure_test()
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->for($author)->create();

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id/comments/$comment->id/vote-status");
        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['direction']])
            ->assertJsonPath('data.direction', null);

        $like = VoteDirection::UP;
        $this
            ->actingAs($author)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/$like->value");

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id/comments/$comment->id/vote-status");
        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['direction']])
            ->assertJsonPath('data.direction', $like->value);

        $dislike = VoteDirection::DOWN;
        $this
            ->actingAs($author)
            ->postJson("/api/posts/$post->id/comments/$comment->id/vote/$dislike->value");

        $response = $this
            ->actingAs($author)
            ->getJson("/api/posts/$post->id/comments/$comment->id/vote-status");
        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['direction']])
            ->assertJsonPath('data.direction', $dislike->value);
    }
}
