# Contributing

Thank you for contributing to the opentracing bundle!

Before we can merge your pull request here are some guidelines that you need to follow. These guidelines do not exist to annoy you, but to keep the code base clean, unified and future proof.

* PHP files should start with this snippet:
    ```php
    <?php

    declare(strict_types=1);
    ```
* PHP files should end with an empty line, and *without* a closing `?>` tag.
* PHP classes should be marked as `final class`.
* PHP classes should usually implement a concise and well documented `interface`. Exceptions to this rule are Symfony specifics, which usually do not have interfaces of their own, e.g. event subscribers or compiler passes.
* PHP classes should have a corresponding test, which should cover all branches. Tests should be designed assertion-first. In the best case, you should write your tests before you write your code, as it will help finding design flaws very early.
* Services should be declared with the fully qualified class name of the service interface in the `services.yaml` and rely on both `autowire: true` and `autoconfigure: true`.
* Services should prefer dependency injection by constructor.
* New features should be covered with functional tests.
* PHP code should respect the PSR-2 coding standard.
* Coding standards are enforced by automated CI builds. You can and should run them manually before you commit code by running:
    ```bash
    composer run-script quality
    ```
* Pull requests may be checked by additional code analysis tools, and these checks may be enforced to pass before merging.

We will always try to review pull requests as soon as possible, but please be patient, if it takes a little bit longer.
