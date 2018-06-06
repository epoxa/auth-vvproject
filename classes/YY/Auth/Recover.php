<?php


namespace YY\Auth;


use YY\Core\Data;
use YY\System\Robot;
use YY\System\Utils;
use YY\System\YY;

class Recover extends Robot
{
    public function __construct($init = null)
    {
        parent::__construct([
            'incarnation' => null,
            'public_key' => null,
            'private_key' => null,
        ]);
    }

    public function init($public_key = null, $private_key = null)
    {
        $incarnation = null;
        if ($public_key) {
            $incarnation = YY::Config('user')->loadFromDatabase([
                'PUBLIC_KEY' => $public_key,
            ]);
        }
        $this['incarnation'] = $incarnation;
        $this['public_key'] = $public_key;
        $this['private_key'] = null;
        // TODO: Reset user language?
        if ($incarnation && $incarnation['CURRENT_KEY'] === $private_key) {
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


    function _PAINT()
    {
        if (Data::_isEqual(YY::$ME, $this['incarnation'])) {
            return;
        }
        ?>
        <div class="container">
            <h1><?= $this->TXT('Recover') ?></h1>
            <table class="table">
                <tr>
                    <td colspan="2"><?= $this->TXT('Something went wrong. Your bookmarklet seems to be invalid. Enter your secret key to restore bookmarklet.') ?></td>
                </tr>
                <?php if($this['incarnation']) : ?>
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
                <?php else : ?>
                    <tr>
                        <td><?= $this->TXT('Public key') ?></td>
                        <td>
                            <form onsubmit="$('input.private-key').focus(); return false;">
                                <?= $this->INPUT('public_key', [
                                    'class' => 'monospace',
                                    'style' => 'width: 264px; font-weight: bold',
                                ]) ?>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><?= $this->TXT('Access key') ?></td>
                    <td>
                        <form onsubmit="go(<?= YY::GetHandle($this) ?>, 'recover'); return false;">
                            <?= $this->INPUT('private_key', [
                                'class' => 'monospace private-key',
//                                'type' => 'hidden',
                                'style' => 'width: 264px; font-weight: bold',
                            ]) ?>
                            <span class="text-info pull-right">
                                <?= $this->CMD('Done', 'recover', ['class' => 'btn btn-sm btn-default']) ?>
                            </span>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $id = '_YY_' . YY::GetHandle($this);
        YY::clientExecute("$('#$id input:first').focus();");
    }


    function recover()
    {
        if (!$this['incarnation']) {
            $this['incarnation'] = YY::Config('user')->loadFromDatabase([
                'PUBLIC_KEY' => $this['public_key'],
            ]);
        }
        if (!$this['incarnation']) {
            $message = YY::Translate('Invalid public key.');
            YY::clientExecute("alert('$message');");
        } else if (empty($this['private_key'])) {
            $message = YY::Translate('Enter your secret key please.');
            YY::clientExecute("alert('$message');");
        } else if ($this['private_key'] !== $this['incarnation']['CURRENT_KEY']) {
            YY::Log('warning', 'Invalid key: ' . $this['private_key'] . '. Need ' . $this['incarnation']['CURRENT_KEY']);
            $message = YY::Translate('This key is invalid. Sorry.');
            YY::clientExecute("alert('$message');");
        } else {
            $this['private_key'] = null;
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
