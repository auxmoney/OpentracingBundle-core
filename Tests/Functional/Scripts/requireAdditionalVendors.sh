#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer require php-http/curl-client nyholm/psr7 webmozart/assert
cd ../../
