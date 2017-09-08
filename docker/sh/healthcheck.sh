#!/usr/bin/env bash

if [[ -f /var/run/php-fpm.pid ]]
then
  exit 0
else
  exit 1
fi
