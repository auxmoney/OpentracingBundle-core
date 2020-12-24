#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
rm -fr vendor/auxmoney/opentracing-bundle-core/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-core
composer require php-http/curl-client nyholm/psr7 webmozart/assert
composer dump-autoload
cd ../../
