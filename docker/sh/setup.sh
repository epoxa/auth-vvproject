#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

mkdir -p runtime/log && chmod a+rwX -R runtime

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

apt-get update && apt-get install -y unzip

docker-php-ext-install pcntl mysqli pdo pdo_mysql

composer update --prefer-dist

./local/prepare-database.sh
