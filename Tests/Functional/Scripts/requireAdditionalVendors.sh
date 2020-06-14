#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer require php-http/curl-client nyholm/psr7 webmozart/assert
cd ../../
