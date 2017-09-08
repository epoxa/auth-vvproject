<?php

use YY\Auth\Main;
use YY\System\YY;

if (isset(YY::$ME['curator'])) {

    $curator = YY::$ME['curator'];

} else {

    $curator = new Main();
    YY::$ME['curator'] = $curator;

}

return $curator;
