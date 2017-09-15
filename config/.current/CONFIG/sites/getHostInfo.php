<?php

use YY\Core\Data;
use YY\System\YY;

/** @var PDO $db */
$db = YY::Config('db')->getConnection();

if (isset($_params['ID'])) {
    $cond = "h.ID = $_params[ID]";
} else if (isset($_params['NAME'])) {
    $cond = "h.NAME = " . $db->quote($_params['NAME']);
} else {
    throw new Exception('No condition for retrieve host info');
}

$res = $db->query("
SELECT
  h.ID, h.NAME, r.REDIRECT_URI
FROM
  hosts h
  LEFT JOIN hosts_registered r ON r.NAME = h.NAME
WHERE
  $cond", PDO::FETCH_ASSOC);

if (!$res) {
  throw new Exception(print_r($db->errorInfo(), true));
}

$data = $res->fetch();
if (!$data) {
    return null;
}
return $data;


