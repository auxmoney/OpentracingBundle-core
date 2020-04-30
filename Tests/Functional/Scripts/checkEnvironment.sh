#!/bin/bash

if [[ -z ${BRANCH} ]]
then
    echo "\$BRANCH is empty, please set it to the current development branch you want to test";
    exit 1;
fi
if [[ -z ${SYMFONY_VERSION} ]]
then
    echo "\$SYMFONY_VERSION is empty, please set it to the target symfony version you want to test against";
    exit 2;
fi
if [[ -z ${PR_ORIGIN} ]]
then
    echo "\$PR_ORIGIN is empty, please set it to the current 'owner/repo' slug you want to test against";
    exit 3;
fi

php -v
composer --version
symfony -V
docker --version
