#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

./vendor/bin/phpunit --verbose
#./vendor/bin/phpunit --verbose --filter "recover"
