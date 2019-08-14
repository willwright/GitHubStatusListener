<?php

namespace MeCodeNinja\GitHubWebhooks;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\Listeners\DevelopBranchListener;
use MeCodeNinja\GitHubWebhooks\Listeners\VendorPathListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class GitHubWebhooksServiceProvider extends ServiceProvider
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
