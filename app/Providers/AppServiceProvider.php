<?php

namespace App\Providers;

use App\Api\Domain\Models\Comment;
use App\Api\Domain\Models\Post;
use App\Api\Domain\Policies\CommentPolicy;
use App\Api\Domain\Policies\PostPolicy;
use App\Api\Infrastructure\Listeners\CommentVoteSubscriber;
use Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        $this->app['events']->subscribe(CommentVoteSubscriber::class);
    }
}
