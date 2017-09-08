<?php

use YY\System\YY;

const SAMPLE_SIZE = 2;
const MIN_LEN = 4;
const MAX_LEN = 15;

/** @var array $_params */

$lang = $_params['language'];
$gender_condition = empty($_params['gender']) ? '' : " AND GENDER = '$_params[gender]'";

/** @var PDO $pdo */

$pdo = YY::Config('db')->getConnection();

$selectTotal = $pdo->prepare("
    SELECT COUNT(*) AS CNT
    FROM names_$lang
    WHERE NAME LIKE ?
");

$selectStart = $pdo->prepare("
    SELECT SUBSTRING(NAME,1, ?) AS START, COUNT(*) AS CNT FROM names_$lang
    WHERE 0 = 0 $gender_condition
    GROUP BY START ORDER BY CNT DESC
");

$selectNext = $pdo->prepare("
    SELECT SUBSTRING(SUBSTRING_INDEX(NAME, :LAST, -1), 1, 1) AS NEXT_LETTER, COUNT(*) AS CNT
    FROM names_$lang
    WHERE NAME LIKE :LIKE $gender_condition
    GROUP BY NEXT_LETTER
    ORDER BY CNT DESC
");

$again = true;

while ($again) {

    $selectTotal->execute(['%']);
    $total = $selectTotal->fetch()['CNT'];

    if (!$total) {
        $err = "Can not find name containing: $name";
        YY::Log('error', $err);
        throw new Exception($err);
    }
    $selected = mt_rand(1, $total);

    $selectStart->execute([SAMPLE_SIZE]);

    $name = null;
    $curr = 0;

    while ($start = $selectStart->fetch()) {
        $curr += $start['CNT'];
        if ($selected <= $curr) {
            $name = $start['START'];
            break;
        }
    }

    assert(isset($name));
    $last = $name;

    while (mb_strlen($name) < MAX_LEN) {

        $selectTotal->execute(["%$last%"]);
        $total = (int)$selectTotal->fetch()['CNT'];
        if (!$total) {
            YY::Log('error', "generateRobotName: no names found like %$last%");
            $name = '';
            break;
        }
        $selected = mt_rand(1, $total);

        $selectNext->execute([
            ':LAST' => $last,
            ':LIKE' => "%$last%",
        ]);

        $nextLetter = null;
        $curr = 0;

        while ($next = $selectNext->fetch()) {
            $curr += (int)$next['CNT'];
            if ($selected <= $curr) {
                $nextLetter = $next['NEXT_LETTER'];
                break;
            }
        }

        assert($nextLetter !== null);

        if ($nextLetter === '') {
            break;
        } else {
            $name .= $nextLetter;
            $last = mb_substr($name, mb_strlen($name) - SAMPLE_SIZE, SAMPLE_SIZE);
        }

    }

    $len = mb_strlen($name);
    if ($len < MIN_LEN || $len >= MAX_LEN) {
        $again = true;
    } else {
        $selectTotal->execute([$name]);
        $again = $selectTotal->fetch()['CNT'] > 0;
    }

}


$name = mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1, mb_strlen($name) - 1);

return $name;
