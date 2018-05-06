<?php

$_SERVER['ENV'] = array (
  'LINKS' => 
  array (
    'MAIN' => 'https://vvproject.com',
    'OVERLAY' => 'https://overlay?PHPUNIT_SELENIUM_TEST_ID=TestOverlay__test_overlay_yandex',
  ),
  'YY_AUTH_MYSQL_DATASOURCE' => 'mysql:host=sql;port=3306;dbname=auth;charset=utf8',
  'YY_AUTH_MYSQL_USER' => 'yy',
  'YY_AUTH_MYSQL_PASSWORD' => 'docker',
  'YY_TRUSTED_IPS' => 
  array (
    0 => '127.0.0.1',
  ),
  'YY_TESTS' => 
  array (
    'YY_TEST_BROWSER' => 'firefox',
    'YY_TEST_SELENIUM_PORT' => 4444,
    'YY_TEST_SELENIUM_HOST' => 'hub',
    'YY_TEST_BASE_URL' => 'https://web/',
  ),
);