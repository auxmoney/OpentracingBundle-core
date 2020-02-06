#!/bin/bash

docker run -d --name jaeger \
  -e COLLECTOR_ZIPKIN_HTTP_PORT=9411 \
  -p 5775:5775/udp \
  -p 6831:6831/udp \
  -p 6832:6832/udp \
  -p 5778:5778 \
  -p 16686:16686 \
  -p 14268:14268 \
  -p 14250:14250 \
  -p 9411:9411 \
  jaegertracing/all-in-one:1.16
docker stop jaeger
mkdir -p build/
cd build/
symfony new --version=${SYMFONY_VERSION} testproject
cd testproject/
composer config minimum-stability dev # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
composer config prefer-stable true    # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
composer require auxmoney/opentracing-bundle-jaeger
cd ../../
