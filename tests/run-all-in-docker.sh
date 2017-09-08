#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/..

docker/tests-up.sh # Tweak php modules and install dependencies

docker-compose -f ./docker/docker-compose.yml exec php /var/www/html/docker/sh/run-tests.sh
