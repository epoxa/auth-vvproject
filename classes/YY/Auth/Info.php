<?php


namespace YY\Auth;


use YY\System\Robot;
use YY\System\YY;

class Info extends Robot
{

    function _PAINT()
    {
        $ageInDays = floor((time() - strtotime(YY::$ME['CAME_DATE'])) / (24 * 3600));
        ?>
        <div class="container">
            <h1><?= htmlspecialchars(YY::$ME['NAME']) ?></h1>
            <table class="table">
                <tr>
                    <td width="200px"><?= $this->TXT('Age') ?></td>
                    <td>
                        <strong><?= $ageInDays ?></strong> <?= $this->TXT('days', ['class' => 'muted']) ?>
                        &nbsp;
                        <?= $this->TXT('Newbie', ['class' => 'label label-info']) ?>
                    </td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Public key') ?></td>
                    <td>
                        <strong class="monospace"><?= YY::$ME['PUBLIC_KEY'] ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Access key') ?></td>
                    <td>
                        <?php if(isset($this['keyDisplayed'])) : ?>
                            <strong class="monospace"><?= YY::$ME['CURRENT_KEY'] ?></strong>
                            <span class="pull-right">
                                <?= $this->CMD('hide', 'hideKey'); ?>
                            </span>
                        <?php else : ?>
                            <strong class="monospace" style="background: #666666">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
                            <span class="pull-right">
                                <?= $this->CMD('display', 'displayKey'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Access button') ?></td>
                    <td>
                       <?= $this->TXT('Already installed') ?>
                        <span class="pull-right">
                            <?= $this->CMD('reinstall', 'reinstall'); ?>
                        </span>
                    </td>
                </tr>
            </table>
            <div class="col-md-12 text-center">
                <?= $this->CMD("Done", 'done', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php
    }

    function displayKey()
    {
        $warning = YY::Translate('Keep your key securely to prevent character stealing!');
        YY::clientExecute('alert(' . json_encode($warning) . ')');
        $this['keyDisplayed'] = true;
    }

    function hideKey()
    {
        unset($this['keyDisplayed']);
    }

    function reinstall()
    {
        YY::$ME['curator']->setPage('creator');
        YY::$ME['curator']['creator']['languageConfirmed'] = true;
    }

    function done()
    {
        YY::$ME['curator']->setPage('info');
        unset(YY::$ME['curator']['successInstalledInfo']);
        if (isset($_SESSION['aim'])) {
            /** @var Aim $aim */
            $aim = $_SESSION['aim'];
            unset($_SESSION['aim']);
            $aim->achieve();
        } else {
            YY::redirectUrl($_SERVER['ENV']['LINKS']['MAIN']);
        };
    }
}
