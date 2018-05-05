<?php


namespace YY\Auth;


use Exception;
use YY\System\Robot;
use YY\System\YY;
use YY\Translation\LanguageSelector;

class Main extends Robot
{

    function __construct()
    {
        parent::__construct();
        $this['languageSelector'] = new LanguageSelector(null);
        if (empty(YY::$ME['newbie'])) {
            if (empty($this['info'])) {
                $this['info'] = new Info();
            }
        }
    }

    function setPage($pageKind)
    {
        if (isset($this[$pageKind])) {
            $this['page'] = $this[$pageKind];
        } else {
            $this['page'] = $this[$pageKind] = $this->makePage($pageKind);
        }
        return $this['page'];
    }

    protected function makePage($kind)
    {
        switch ($kind) {
            case 'creator': return new CharacterCreator();
            case 'success': return new SuccessMessage();
            case 'info': return new Info();
            case 'recover': return new Recover();
            default: throw new Exception("Unknown page kind: $kind");
        }
    }

    protected function _PAINT()
    {
        if (isset(YY::$CURRENT_VIEW['TRANSLATOR'])) {
            YY::$CURRENT_VIEW['TRANSLATOR']->_SHOW();
        }
        $this['languageSelector']->_SHOW();
        if (empty($this['page'])) {
            ?>
            <div class="container">
                <h1><?= $this->TXT('Authentication') ?></h1>

                <p><?= $this->TXT("You are not entered now.") ?> <?= $this->TXT("To log in as existing character just click your bookmarklet with one's name.") ?></p>

                <h1><?= $this->TXT('Sign up') ?></h1>

                <p><?= $this->TXT("You can create new character in three steps:") ?></p>
                <ol>
                    <li><?= $this->TXT("Select preferred language") ?></li>
                    <li><?= $this->TXT("Select character name") ?></li>
                    <li><?= $this->TXT("Create your personal bookmarklet") ?></li>
                </ol>
                <?=
                $this->CMD('Create new character', 'createCharacter', [
                    'class' => 'btn btn-primary pull-right',
                    'beforeContent' => '<i class="fa fa-plus"></i>&nbsp;',
                ]) ?>
            </div>
            <?php
        } else {
            $this['page']->_SHOW();
        }
    }

    function createCharacter()
    {
        unset(YY::$ME['newbie']);
        $this['creator'] = new CharacterCreator();
        $this->setPage('creator');
        $this['languageSelector']['callback'] = [
            'robot' => $this['creator'],
            'method' => 'eventLanguageChanged',
        ];
    }


}
