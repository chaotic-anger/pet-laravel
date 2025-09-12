<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
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
            'title' => $this->faker->sentence(),   // случайное предложение
            'content' => $this->faker->paragraph() // случайный абзац
        ];
    }

    public function withComments(int $count = null): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            Comment::factory()
                ->count($count ?? rand(1, 5)) // по умолчанию от 1 до 5 комментариев
                ->for($post)
                ->create();
        });
    }
}
