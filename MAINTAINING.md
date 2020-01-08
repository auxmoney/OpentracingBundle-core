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

### Setup

* Install [NodeJS](https://nodejs.org/).
* Obtain a [personal access token](https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line#creating-a-token).
* Add the token to your environment as `CONVENTIONAL_GITHUB_RELEASER_TOKEN`, e.g.:
    ```bash
    export CONVENTIONAL_GITHUB_RELEASER_TOKEN=63d0cea9d550e495fde1b81310951bd7
    ```

### Creating a release

* Create a new release commit by executing:
    ```bash
    npx standard-version@7.1
    ```
* Review and push the commit (including the tag) to `origin`:
    ```bash
    git push --follow-tags origin master
    ```
* Create a release at GitHub by executing:
    ```bash
    npx conventional-github-releaser
    ```
