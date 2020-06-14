#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer config use-github-api false
CORE_VERSION=`composer show auxmoney/opentracing-bundle-core | grep versions | grep -o -E '\*\ .+' | cut -d' ' -f2 | cut -d',' -f1`
composer require auxmoney/opentracing-bundle-core:"dev-${BRANCH} as ${CORE_VERSION}"
composer require php-http/curl-client nyholm/psr7 webmozart/assert
cd ../../
