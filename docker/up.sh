#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/..

docker-compose -f docker/docker-compose.yml -f docker/docker-compose.production.override.yml up -d --remove-orphans

printf "Waiting for setup "
until docker-compose -f ./docker/docker-compose.yml exec php /var/www/html/docker/sh/healthcheck.sh
do
  sleep 3s
  printf .
done

echo
echo "Web server is ready"
