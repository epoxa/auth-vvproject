<?php

require_once __DIR__ . "/../config/env.php";
//require_once __DIR__ . "/../config/config.php";
//require_once __DIR__ . "/../vendor/autoload.php";

$db = new PDO(
    $_SERVER['ENV']['YY_AUTH_MYSQL_DATASOURCE'], $_SERVER['ENV']['YY_AUTH_MYSQL_USER'], $_SERVER['ENV']['YY_AUTH_MYSQL_PASSWORD']
);

$db->exec('SET NAMES UTF8');

$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

if (count($tables)) {
    throw new Exception("Database is not empty! Can not create tables!");
}

$script = file_get_contents(__DIR__ . '/../db/auth-setup.sql');

$db->exec($script);
