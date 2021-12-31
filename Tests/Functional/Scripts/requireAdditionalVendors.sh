#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
VENDOR_VERSION=""
CURRENT_REF=${GITHUB_HEAD_REF:-$GITHUB_REF}
CURRENT_BRANCH=${CURRENT_REF#refs/heads/}
if [ "$CURRENT_BRANCH" != "master" ]; then
    composer config minimum-stability dev
    composer config extra.branch-alias.dev-$CURRENT_BRANCH "1.99.0"
    VENDOR_VERSION=":1.99.0"
fi
composer require auxmoney/opentracing-bundle-core${VENDOR_VERSION} auxmoney/opentracing-bundle-jaeger
composer require php-http/curl-client nyholm/psr7 webmozart/assert
composer dump-autoload
cd ../../
