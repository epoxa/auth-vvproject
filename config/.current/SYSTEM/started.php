<?php

/**
 * This handler called every time client request process starting
 */

use YY\System\YY;

//YY::Log('REQUEST_URI: ' . $_SERVER['REQUEST_URI']);

if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])  && substr($_SERVER['REQUEST_URI'],0,6) === '/token') {

    // Does not use $ME nor database connection either.

    $answer = [];
    $httpCode = null;

    if (isset($_REQUEST['state'])) {
        $answer['state'] = $_REQUEST['state'];
    }

    if (!isset($_REQUEST['code']) || !isset($_REQUEST['redirect_uri'])) {

        $answer['error'] = 'parameter_absent';
        $answer['error_description'] = 'Provide both code and redirect_uri parameters please';
        $httpCode = 400;

    } else if (!preg_match('/^[a-f0-9]{32}$/', $_REQUEST['code'])) {

        $answer['error'] = 'invalid_request';
        $answer['error_description'] = 'Wrong formatted authorization code';
        $httpCode = 400;

    } else {

        $data = YY::Config('tokens')->utilize(['token' => $_REQUEST['code']]);

        if (!$data) {

            $answer['error'] = 'invalid_grant';
            $answer['error_description'] = 'Authorization code is invalid or already used';
            $httpCode = 400;

        } else if ($data['redirect_uri'] !== $_REQUEST['redirect_uri']) {

            $answer['error'] = 'invalid_grant';
            $answer['error_description'] = 'Wrong redirect_uri parameter provided: ' . $_REQUEST['redirect_uri'];
            $httpCode = 400;

        } else {

            $answer['access_token'] = 'public';
            $answer['token_type'] = 'public';
            $answer['scope'] = 'public';
            $answer['public_key'] = $data['public_key'];
            $answer['name'] = $data['name'];
            $answer['language'] = $data['language'];
            $answer['age'] = $data['age'];
            $answer['active_days'] = $data['active_days'];

        }

    }

    if ($httpCode) {
        http_response_code($httpCode);
    }
    file_put_contents(LOG_DIR . 'last_token_answer.txt', print_r($answer, true));
    YY::sendJson($answer);
    exit;
}
