<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory(),
        ];
    }

    public function withComments(int $count = null): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            Comment::factory()
                ->count($count ?? rand(1, 5))
                ->for($post, 'post')
                ->for(User::factory())
                ->create();
        });
    }
}
