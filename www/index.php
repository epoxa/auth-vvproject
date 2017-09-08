<?php

use YY\System\YY;

if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
//    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
//        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//        http_response_code($_SERVER['REQUEST_METHOD'] === 'GET' ? 302 : 307);
//        exit;
//    }
}

require_once "../config/env.php";
require_once "../config/config.php";
require_once "../vendor/autoload.php";

YY::Run();
