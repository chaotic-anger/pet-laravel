<?php

namespace App\Providers;

use App\Listeners\CommentVoteSubscriber;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app['events']->subscribe(CommentVoteSubscriber::class);
    }
}
