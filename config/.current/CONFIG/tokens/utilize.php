<?php

/** @var array $_params */

$fileName = TOKENS_DIR . $_params['token'];

if (!file_exists($fileName)) return null;

$data = json_decode(file_get_contents($fileName), true);

//unlink($fileName); // TODO in production

return $data;
