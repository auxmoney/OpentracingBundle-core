#!/bin/bash

if [[ -z ${SYMFONY_VERSION} ]]
then
    echo "\$SYMFONY_VERSION is empty, please set it to the target symfony version you want to test against";
    exit 1;
fi

php -v
composer --version
symfony -V
docker --version
