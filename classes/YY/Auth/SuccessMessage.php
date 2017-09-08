<?php


namespace YY\Auth;


use YY\System\Robot;
use YY\System\YY;

class SuccessMessage extends Robot
{
    protected function _PAINT()
    {
        ?>
        <div class="container">
            <h1><?= $this->TXT("Bookmarklet installed") ?></h1>
            <div class="well clearfix">
                <?= $this->TXT("The last version of bookmarklet installed successfully") ?>
            </div>
            <div class="col-md-12 text-center">
                <?= $this->CMD("Done", 'done') ?>
            </div>
        </div>
        <?php
    }

    function done()
    {
        YY::$ME['curator']->setPage('info');
        unset(YY::$ME['curator']['successInstalledInfo']);
        if (isset($_SESSION['aim'])) {
            $aim = $_SESSION['aim'];
            unset($_SESSION['aim']);
            $aim->{$aim['type']}(); // oauth, ...
        };
    }

}
