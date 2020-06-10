#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer config use-github-api false
composer require auxmoney/opentracing-bundle-core:"dev-${BRANCH} as 0.6.7"
composer require php-http/curl-client nyholm/psr7 webmozart/assert
cd ../../
