#!/usr/bin/env bash

cd $(cd $(dirname $0) && pwd)/../../

pecl install xdebug
docker-php-ext-enable xdebug

echo "auto_prepend_file = /var/www/html/docker/app/auth/prepend.php" >> /usr/local/etc/php/php.ini
echo "auto_append_file = /var/www/html/docker/app/auth/append.php" >> /usr/local/etc/php/php.ini

#echo "auto_prepend_file = /var/www/html/vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/prepend.php" >> /usr/local/etc/php/php.ini
#echo "auto_append_file = /var/www/html/vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/append.php" >> /usr/local/etc/php/php.ini
