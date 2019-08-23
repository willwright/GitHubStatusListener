<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configured Checks
    |--------------------------------------------------------------------------
    |
    | The checks defined here are made available to reference in the repository specific configuration of
    | GitHubWebhooks.  This configuration allows developers to add their own checks.
    |
    */
    'checks' => [
        'BranchCheck' => \MeCodeNinja\GitHubWebhooks\Check\BranchCheck::class,
        'PathCheck' => \MeCodeNinja\GitHubWebhooks\Check\PathCheck::class,
    ]
];