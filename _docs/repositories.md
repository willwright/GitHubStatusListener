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

