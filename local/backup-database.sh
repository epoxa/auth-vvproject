#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/..

mysqldump -h127.0.0.1 -P4306 -uyy -p auth > db/auth-dump.sql
