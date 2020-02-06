#!/bin/bash

rm -fr build/testproject
docker rm jaeger
sudo killall php-fpm || true # only god knows why the pool worker does not terminate
