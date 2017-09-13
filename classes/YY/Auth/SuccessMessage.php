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
            <p><?= $this->TXT(["Hello %s", YY::$ME['NAME']]) ?></p>
            <p><?= $this->TXT("The last version of bookmarklet installed successfully") ?></p>
            <p><?= $this->TXT("Keep your key securely to prevent character stealing") ?></p>
            <div class="col-md-12 text-center">
                <?= $this->CMD("Done", 'done', ['class' => 'btn btn-primary']) ?>
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
