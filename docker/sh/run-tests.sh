#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

./vendor/bin/phpunit --coverage-clover /var/www/html/runtime/log/coverage.xml --whitelist /var/www/html/config --whitelist /var/www/html/config/.current --whitelist /var/www/html/classes/YY --whitelist /var/www/html/classes/Translation
#./vendor/bin/phpunit --filter test_infrastructure
