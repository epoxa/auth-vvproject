<?php

$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = __DIR__ . '/../../../runtime';
if (preg_match('/phpunit_coverage/', $_SERVER['REQUEST_URI'])) chdir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']);
require __DIR__ . '/../../../vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/prepend.php';
