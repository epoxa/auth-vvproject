#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

echo "ls:"
ls -l

echo "ls ./vendor"
ls ./vendor

echo "ls ./vendor/bin"
ls ./vendor/bin

./vendor/bin/phpunit
