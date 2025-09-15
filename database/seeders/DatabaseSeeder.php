<?php

namespace Database\Seeders;

use App\Api\Models\Comment;
use App\Api\Models\CommentVote;
use App\Api\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $author = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $users = User::factory()->count(10)->create();
        Post::factory()->for($author)->count(15)->withComments(3)->create();

        foreach (Comment::all() as $comment) {
            $voters = $users->shuffle()->take(rand(1, 5));
            foreach ($voters as $user) {
                CommentVote::factory()->for($comment, 'comment')->for($user, 'user')->create();
            }
        }
    }
}
