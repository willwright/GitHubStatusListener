<?php

namespace MeCodeNinja\GitHubWebhooks\Listeners;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;

interface StatusListenerInterface
{
    function handle(PullRequest $event);
}