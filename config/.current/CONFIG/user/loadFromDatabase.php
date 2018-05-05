<?php

use YY\Core\Data;
use YY\System\YY;

if (isset($_params['ID'])) {
    $cond = "ID = $_params[ID]";
} else if (isset($_params['PUBLIC_KEY'])) {
    $cond = "PUBLIC_KEY = '$_params[PUBLIC_KEY]'";
} else if (isset($_params['CURRENT_KEY'])) {
    $cond = "CURRENT_KEY = '$_params[CURRENT_KEY]'";
} else {
    throw new Exception('No condition for load user from database');
}

/** @var PDO $db */
$db = YY::Config('db')->getConnection();

$data = $db->query("SELECT * FROM users WHERE $cond", PDO::FETCH_ASSOC)->fetch();

if (!$data) {
    return null;
}

$existing = Data::_load($data['INCARNATION']);

if ($existing) {

    $user = $existing;

} else {

    $data['_YYID'] = $data['INCARNATION'];
    $data['nameConfirmed']  = true; // TODO: Really? Why??
    $user = new YY($data);
    $user->_REF;
    $user['VIEWS'] = [];
    foreach($this['storedProperties'] as $propKey => $type) {
        if ($type === 'date') {
            $user[$propKey] = strtotime($user[$propKey]);
        }
    }

}

return $user;
