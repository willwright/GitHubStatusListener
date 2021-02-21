<?php

namespace MeCodeNinja\GitHubWebhooks\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\Listeners\PullRequestListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PullRequest::class => [
            PullRequestListener::class
        ]
    ];

    public function boot() {
        parent::boot();
    }
}