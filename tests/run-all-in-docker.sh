#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/..

# Tweak php modules and install dependencies
docker/tests-up.sh

# Set browser according to environment variable
docker-compose -f ./docker/docker-compose.yml exec php /var/www/html/docker/sh/set-test-browser.sh "${YY_TEST_BROWSER}"

# Restart world
rm -rfd runtime/data/*

# Run tests
docker-compose -f ./docker/docker-compose.yml exec php /var/www/html/docker/sh/run-tests.sh
