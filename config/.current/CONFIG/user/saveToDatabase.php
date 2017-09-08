<?php

/** @var array $_params */

use YY\System\YY;

/** @var PDO $db */
$db = YY::Config('db')->getConnection();

$user = $_params['user'];

if (isset($user['ID'])) {

    $updates = [];
    foreach($this['storedProperties'] as $propKey => $type) {
        if (isset($user[$propKey]) && $user[$propKey] !== null) {

            $val = $user[$propKey];
            if ($type === '' /* string */) {
                $val = $db->quote($val);
            } else if ($type === 'date') {
                $val = "FROM_UNIXTIME('$val')";
            };
            $updates[] = "$propKey = $val";

//        } else {
//
//            $updates[] = "$propKey = NULL";

        }
    }

    $db->exec("UPDATE users SET " . implode(', ', $updates) . " WHERE ID = $user[ID]");

} else {

    $columns = [];
    $values = [];

    foreach($this['storedProperties'] as $propKey => $type) {

        $columns[] = $propKey;

        if (isset($user[$propKey]) && $user[$propKey] !== null) {

            $val = $user[$propKey];
            if ($type === '' /* string */) {
                $val = $db->quote($val);
            } else if ($type === 'date') {
                $val = "FROM_UNIXTIME('$val')";
            };
            $values[] = $val;

        } else {

            $values[] = 'NULL';

        }
    }

    $columns = implode(',', $columns);
    $values = implode(', ', $values);
    $sql = "INSERT INTO users($columns) VALUES ($values)";
    $affected = $db->exec($sql);
    if ($affected && !! $id = $db->lastInsertId('ID')) {
        $user['ID'] = $id;
        $newbies = YY::$WORLD['NEWBIES'];
        if (!$newbies->_acquireExclusiveAccess()) {
            throw new Exception('Can not lock newbies list');
        };
        unset($newbies[$user]);
    } else {
        $err = $db->errorInfo();
        throw new Exception(array_pop($err));
    }

}

