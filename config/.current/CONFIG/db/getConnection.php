<?php

use YY\System\Utils;
use YY\System\YY;

static $DB_CONNECTION;

if ($DB_CONNECTION) return $DB_CONNECTION;

$DB_CONNECTION = new PDO(
    $_SERVER['ENV']['YY_AUTH_MYSQL_DATASOURCE'], $_SERVER['ENV']['YY_AUTH_MYSQL_USER'], $_SERVER['ENV']['YY_AUTH_MYSQL_PASSWORD']
);

$DB_CONNECTION->exec('SET NAMES UTF8');

return $DB_CONNECTION;