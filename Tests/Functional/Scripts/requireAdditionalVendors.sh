#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
VENDOR_VERSION=""
CURRENT_REF=${GITHUB_HEAD_REF:-$GITHUB_REF}
CURRENT_BRANCH=${CURRENT_REF#refs/heads/}
if [ "$CURRENT_BRANCH" != "master" ]; then
    composer config minimum-stability dev
    composer config repositories.fork vcs ${GITHUB_SERVER_URL}/${GITHUB_REPOSITORY}.git
    VENDOR_VERSION=:"dev-${CURRENT_BRANCH} as 1.99.0"

fi
composer require "auxmoney/opentracing-bundle-core${VENDOR_VERSION}" auxmoney/opentracing-bundle-jaeger
composer require php-http/curl-client nyholm/psr7 webmozart/assert
composer dump-autoload
cd ../../
