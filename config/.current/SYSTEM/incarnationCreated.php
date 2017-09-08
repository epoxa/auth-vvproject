<?php

use YY\Core\Data;
use YY\Core\Ref;
use YY\System\YY;

if (empty(YY::$WORLD['NEWBIES'])) {
    YY::$WORLD['NEWBIES'] = [];
}
/** @var Data $newbies */
$newbies = YY::$WORLD['NEWBIES'];
if (!$newbies->_acquireExclusiveAccess()) {
    throw new Exception('Can not lock newbies list');
};
$newbies[YY::$ME] = new Ref(YY::$ME, true);
YY::$ME['newbie'] = true;

$lang = isset(YY::$CURRENT_VIEW['LANGUAGE']) ? YY::$CURRENT_VIEW['LANGUAGE'] : 'en';
$gender = null;

YY::$ME['NAME'] = YY::Config('user')->generateName(['language' => $lang, 'gender' => $gender]);
YY::$ME['PUBLIC_KEY'] = md5(uniqid('', true) . time());
YY::$ME['STATE'] = 0;
YY::Config('user')->touch([
   'user' => YY::$ME,
]);

