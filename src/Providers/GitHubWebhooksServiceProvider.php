<?php

namespace MeCodeNinja\GitHubWebhooks\Providers;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\GitHub\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use MeCodeNinja\GitHubWebhooks\Listeners\PullRequestListener;

class GitHubWebhooksServiceProvider extends ServiceProvider
{
    const CONFIG_FILE = 'config/githubwebhooks.php';

    /**
     * Bootstrap services.
     *
     * @param Config $config
     * @return void
     */
    public function boot(Config $config)
    {
        //Bind Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        //Make sure config file exists
        $config->createRepoConfigFile();

        //Publish config file
        $this->publishes([
            __DIR__. DIRECTORY_SEPARATOR . '../' . self::CONFIG_FILE => config_path('githubwebhooks.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
    }
}
