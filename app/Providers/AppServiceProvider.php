<?php

namespace App\Providers;

use App\Listeners\CommentVoteSubscriber;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['events']->subscribe(CommentVoteSubscriber::class);
    }
}
