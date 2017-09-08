<?php

// To update statistics and last activity info

$user = $_params['user'];

$now = time();

$lastActivity = empty($user['LAST_ACTIVITY']) ? null : $user['LAST_ACTIVITY'];

if (empty($user['CAME_DATE'])) {
    $user['CAME_DATE'] = $now;
    $user['CAME_IP'] = $_SERVER['REMOTE_ADDR'];
    $user['CAME_FROM'] = isset($_SERVER['HTTP_REFERER']) ? ($_SERVER['HTTP_REFERER']) : null;
}
$user['INCARNATION'] = $user->_YYID;
$user['LAST_ACTIVITY'] = $now;
$user['LAST_IP'] = $_SERVER['REMOTE_ADDR'];

if ($lastActivity && date('Y-m-d', $lastActivity) !== date('Y-m-d', $now)) {
    $user['ACTIVE_DAYS'] = $user['ACTIVE_DAYS'] + 1;
    $user['LAST_HITS'] = 1;
} else {
    $user['ACTIVE_DAYS'] = 1;
    $user['LAST_HITS'] = (isset($user['LAST_HITS']) ? $user['LAST_HITS'] : 0) + 1;
}

