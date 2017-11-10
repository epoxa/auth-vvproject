#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

./vendor/bin/phpunit --coverage-clover /var/www/html/runtime/log/coverage.xml --whitelist /var/www/html
#./vendor/bin/phpunit --filter test_infrastructure
