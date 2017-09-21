<?php

use YY\System\YY;

/** @var array $_params */

if (!file_exists(TOKENS_DIR)) {
    mkdir(TOKENS_DIR, 0777, true);
}

$token = isset($_params['token']) ? $_params['token'] : YY::GenerateNewYYID();
$fileName = TOKENS_DIR . $token;

return file_put_contents($fileName, json_encode($_params['data'])) ? $token : null;



