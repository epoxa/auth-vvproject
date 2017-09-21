<?php

use YY\System\YY;

/** @var array $_params */

$bare_redirect_uri = $_params['redirect_uri'];
$state = isset($_params['state']) ? $_params['state'] : 'public';
$user = $_params['user'];

$code = YY::GenerateNewYYID();

$url = parse_url($bare_redirect_uri);
$url["query"] = (isset($url["query"]) ? $url["query"] . '&' : '') . "code=$code&state=$state";

$full_redirect_uri =
    ((isset($url["scheme"])) ? $url["scheme"] . "://" : "")
    . ((isset($url["user"])) ? $url["user"]
        . ((isset($url["pass"])) ? ":" . $url["pass"] : "") . "@" : "")
    . ((isset($url["host"])) ? $url["host"] : "")
    . ((isset($url["port"])) ? ":" . $url["port"] : "")
    . ((isset($url["path"])) ? $url["path"] : "")
    . ("?" . $url["query"])
    . ((isset($url["fragment"])) ? "#" . $url["fragment"] : "")
;

$user_info = [
    'public_key' => $user['PUBLIC_KEY'],
    'name' => $user['NAME'],
    'language' => isset($user['LANGUAGE']) ? $user['LANGUAGE'] : null,
    'age' => floor((time() - $user['CAME_DATE']) / (24 * 3600)),
    'active_days' => $user['ACTIVE_DAYS'],
    'redirect_uri' => $bare_redirect_uri,
];

return $this->create(['token' => $code, 'data' => $user_info]) ? $full_redirect_uri : null;

