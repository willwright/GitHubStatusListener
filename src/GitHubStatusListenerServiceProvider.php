<?php

namespace MeCodeNinja\GitHubStatusListener;

use App\Events\GitHub\PullRequest;
use App\Listeners\GitHub\DevelopBranchListener;
use App\Listeners\GitHub\VendorPathListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;


class GitHubStatusListenerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //Bind Event Listeners
        Event::listen(PullRequest::class, DevelopBranchListener::class);
        Event::listen(PullRequest::class, VendorPathListener::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
