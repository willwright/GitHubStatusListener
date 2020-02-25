---
title: Extending
description: Extending
---
# Extending
The developer may have their own checks that they want to perform.  In order to create a new type of check the developer should

1. Create a new Check Class which extends `\MeCodeNinja\GitHubWebhooks\Check\CheckAbstract`
2. Implement the methods required by the interface
3. Register your Class in the `checks` array of `config/githubwebhooks.php`