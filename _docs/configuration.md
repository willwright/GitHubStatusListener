---
title: Configuration
description: Configuration
---

# Repositories
As many repository items can be added to the Repositories sequence as is desired.

The `repositories` sequence will be queried to build a set of Repositories which the `PullRequest` matches. Either the name must match exactly OR a wildcard can be used.

Example:
Given `PullRequest.repo.full_name` = "willwright/githubwebhooks".

`respository.name` = "willwright/githubwebhooks" will match

`respository.name` = "*" will match

`respository.name` = "notmy/repo" will not match

```yaml
repositories:
  -
    name: willwright/githubwebhooks
    token: 123456
    checks:
      BranchCheck:
        branches: [origin/develop]
      PathCheck:
        paths: [/^vendor\//]
  -
    name: '*'
    token: 123456
    checks:
      PathCheck:
        paths: [/^somepath\//]
  -
    name: 'notmy/repo'
    token: 123456
    checks:
      PathCheck:
        paths: [/^somepath\//]
``` 

# Check Types
Checks are the Class that actually do work.  Each **must** extend `\MeCodeNinja\GitHubWebhooks\Check\CheckAbstract`.
Checks are made available for use to the Factory via `config/githubwebhooks.php`.

This package comes with:
* `\MeCodeNinja\GitHubWebhooks\Check\BranchCheck`
* `\MeCodeNinja\GitHubWebhooks\Check\PathCheck`

The Key used in the repository configuration can be found from `config/githubwebhooks.php`.  They are:
* BranchCheck
* PathCheck

### BranchCheck
Branch request is designed to ensure that feature branch has not been forked from a particular branch and has not had a
particular branch merged into it.

Many teams follow the Git Branching strtegy in which all feature branches must be forked from master and if develop were
to be merged in this would spoil the branch from being merged back into master.

Branch check takes an array of strings.  The Pull Request branch will be checked to verify it has not been forked from
the branch in question.  The Pull Request branch will be checked to make sure that it has not had the branch in question
merged into it.

Note that due to Git's commit and branchng internal architecture determining whether a branch has had another branch
 merged into it or not is not an exact science and this check is prone to false positives.
 
 Example
 ```yaml
      BranchCheck:
        branches: [origin/develop]

```
The example will result in the Pull Request branch being checked for `orign/develop`

### PathCheck
PathCheck is designed to ensure that a specific path has not been modified in a feature branch.

Many modern applciations are built from frameworks or groups of modules.  Best practice is to extend the libraries
 to create a unique functionality not to modify the libraries themselves.
  
Path check takes an array of regular expression patterns.  It will check the collection of changed files in the Pull Request for a match.
If a match it found then the check will report a failure. 

Example
```yaml
      PathCheck:
        paths: [/^vendor\//]

```

The example will check the collection of changed paths in the Pull Request for `/vendor`.  If found the check will report a failure.

