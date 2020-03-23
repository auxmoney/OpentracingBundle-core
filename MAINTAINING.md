# Maintaining

## Pull requests

* Pull requests should be merged only if it was reviewed successfully and all checks have passed.
* Pull requests must be merged with "squash" commits. These squash commits should contain the contributors' GitHub aliases as additional information, e.g.:
    ```
    feat: awesome feature (#1)
    
    contributors: @contributor1, @contributor2, ...
    ```
* Commit messages for squash commits must follow the [Conventional Commits specification](https://www.conventionalcommits.org/en/v1.0.0/).

## Releasing

Releases are published automatically with [semantic-release](https://github.com/semantic-release/semantic-release) run in [GitHub Actions](https://github.com/features/actions).
