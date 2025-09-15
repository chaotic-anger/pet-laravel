<?php

namespace App\Providers;

use App\Api\Events\Listeners\CommentVoteSubscriber;
use App\Api\Models\Comment;
use App\Api\Models\Post;
use App\Api\Policies\CommentPolicy;
use App\Api\Policies\PostPolicy;
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
