<?php


namespace YY\Auth;


use YY\System\Robot;
use YY\System\Utils;
use YY\System\YY;

class Recover extends Robot
{
    public function __construct($init = null)
    {
        parent::__construct($init);
        if (isset($_SESSION['auth_guest']) && preg_match('/^[0-9a-f]{32}$/', $_SESSION['auth_guest'])) {
            $incarnation =  YY::Config('user')->loadFromDatabase([
                'PUBLIC_KEY' => $_SESSION['auth_guest'],
            ]);
            if ($incarnation) {
                $this['incarnation'] = $incarnation;
            }
        }
        if (empty($this['incarnation'])) $this['incarnation'] = YY::$ME;
        $this['privateKey'] = null;
        // TODO: Reset user language
    }


    function _PAINT()
    {
        ?>
        <div class="container">
            <h1><?= $this->TXT('Recover') ?></h1>
            <table class="table">
                <tr>
                    <td colspan="2"><?= $this->TXT('Something went wrong. Your bookmarklet seems to be invalid. Enter your secret key to restore bookmarklet.') ?></td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Character name') ?></td>
                    <td>
                        <strong><?= $this['incarnation']['NAME'] ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Public key') ?></td>
                    <td>
                        <strong class="monospace"><?= $this['incarnation']['PUBLIC_KEY'] ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><?= $this->TXT('Access key') ?></td>
                    <td>
                        <form onsubmit="go(<?= YY::GetHandle($this) ?>, 'recover'); return false;">
                            <?= $this->INPUT('privateKey', [
                                'class' => 'monospace',
                                'style' => 'width: 264px; font-weight: bold',
    //                            'placeholder' => '................................',
    //                            'placeholder' => '--------------------------------',
                            ]) ?>
                            <span class="text-info pull-right">
                                <?= $this->CMD('Done', 'recover', ['class' => 'btn btn-sm btn-default']) ?>
                            </span>
                        <form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $id = '_YY_' . YY::GetHandle($this);
        YY::clientExecute("$('#$id input').focus();");
    }


    function recover()
    {
        if (empty($this['privateKey'])) {
            $message = YY::Translate('Enter your secret key please.');
            YY::clientExecute("alert('$message');");
        } else if ($this['privateKey'] !== $this['incarnation']['CURRENT_KEY']) {
            $message = YY::Translate('This key is invalid. Sorry.');
            YY::clientExecute("alert('$message');");
        } else {
            $this['privateKey'] = null;
            YY::$ME = $this['incarnation'];
            Utils::UpdateSession(YY::$ME->_YYID);
            /** @var Main $main */
            $main = YY::Config('user')->getMainCurator();
            $main->setPage('creator');
            $main['creator']['languageConfirmed'] = true;
            YY::$ME['nameConfirmed'] = true;
            YY::redirectUrl();
        }
    }
}
