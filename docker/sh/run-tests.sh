#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

./vendor/bin/phpunit
#./vendor/bin/phpunit --filter test_language_edit_mode
