<?php

namespace MeCodeNinja\GitHubWebhooks;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\GitHub\Config;
use MeCodeNinja\GitHubWebhooks\Listeners\BranchCheck;
use MeCodeNinja\GitHubWebhooks\Listeners\PathCheck;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use MeCodeNinja\GitHubWebhooks\Listeners\PullRequestListener;

class GitHubWebhooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Config $config)
    {
        //Bind Event Listeners
        Event::listen(PullRequest::class, PullRequestListener::class);

        //Bind Routes
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        //Make sure config file exists
        $config->createRepoConfigFile();
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
