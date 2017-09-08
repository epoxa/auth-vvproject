#!/usr/bin/env bash

docker-compose -f $(cd $(dirname $0) && pwd)/docker-compose.yml down --remove-orphans
