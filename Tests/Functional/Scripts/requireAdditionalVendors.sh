#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
VENDOR_VERSION=""
CURRENT_REF=${GITHUB_HEAD_REF:-$GITHUB_REF}
CURRENT_BRANCH=${CURRENT_REF#refs/heads/}
if [ "$CURRENT_BRANCH" != "master" ]; then
    composer config minimum-stability dev
    VENDOR_VERSION=":dev-${CURRENT_BRANCH}"
fi




###
echo "CURRENT_BRANCH: $CURRENT_BRANCH"
if [[ $CURRENT_BRANCH -ne "master" ]]; then
    echo "$CURRENT_BRANCH -ne \"master\""
fi
if [[ $CURRENT_BRANCH -eq "master" ]]; then
    echo "$CURRENT_BRANCH -eq \"master\""
fi
if [[ $CURRENT_BRANCH != "master" ]]; then
    echo "$CURRENT_BRANCH != \"master\""
fi
if [[ $CURRENT_BRANCH == "master" ]]; then
    echo "$CURRENT_BRANCH == \"master\""
fi
if [[ "$CURRENT_BRANCH" -ne "master" ]]; then
    echo "\"$CURRENT_BRANCH\" -ne \"master\""
fi
if [[ "$CURRENT_BRANCH" -eq "master" ]]; then
    echo "\"$CURRENT_BRANCH\" -eq \"master\""
fi
if [[ "$CURRENT_BRANCH" != "master" ]]; then
    echo "\"$CURRENT_BRANCH\" != \"master\""
fi
if [[ "$CURRENT_BRANCH" == "master" ]]; then
    echo "\"$CURRENT_BRANCH\" == \"master\""
fi
echo "VENDOR_VERSION: $VENDOR_VERSION"
exit 1
###




composer require auxmoney/opentracing-bundle-core${VENDOR_VERSION} auxmoney/opentracing-bundle-jaeger
composer require php-http/curl-client nyholm/psr7 webmozart/assert
composer dump-autoload
cd ../../
