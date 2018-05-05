#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/..

docker-compose -f docker/docker-compose.yml up -d --remove-orphans
