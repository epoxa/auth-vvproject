<?php


namespace YY\Auth;


use YY\System\Robot;
use YY\System\YY;

class CharacterCreator extends Robot
{

    function _PAINT()
    {
        $lang = isset(YY::$CURRENT_VIEW['LANGUAGE']) ? YY::$CURRENT_VIEW['LANGUAGE'] : null;
        $language = isset($lang, YY::Config('LANGUAGES')[$lang], YY::Config('LANGUAGES')[$lang]['']) ? YY::Config('LANGUAGES')[$lang][''] : $this->TXT('None selected');
        ?>
        <div class="container">
            <h1><?= $this->TXT('Create character') ?></h1>
            <div class="well clearfix">
                <span><?= $this->TXT('1. Language') ?></span>
                <?php if (empty($this['languageConfirmed'])) : ?>
                    <span class="text-info pull-right">
                        <span class="fa fa-gear fa-spin icon-larger pull-right"></span>
                        <br>
                        <br>
                        <?php if($lang) : ?>
                            <?= $this->CMD('Done', 'setLanguage', ['class' => 'btn btn-sm btn-default']) ?>
                        <?php else : ?>
                            <?php  YY::$ME['curator']['languageSelector']->openLangMenu(); ?>
                        <?php endif; ?>
                    </span>
                <?php else : ?>
                    <span class="text-success pull-right"><span class="fa fa-check icon-larger"></span></span>
                <?php endif; ?>
                <span>
                    <strong>
                        <?= $language ?>
                    </strong>
                    <br>
                    <?= $this->TXT('You can always change current language later', ['class' => 'text-muted']) ?>
                </span>
            </div>
            <div class="well clearfix">
                <span><?= $this->TXT('2. Nickname') ?></span>
                <?php if (empty($this['languageConfirmed'])) : ?>
                    <br>
                    <?= $this->TXT('Select language first', ['class' => 'text-muted']) ?>
                <?php elseif (isset(YY::$ME['nameConfirmed'])) : ?>
                    <span class="text-success pull-right"><span class="fa fa-check icon-larger"></span></span>
                    <strong><?= YY::$ME['NAME'] ?></strong>
                    <br>
                    <?= $this->TXT('Great choice!', ['class' => 'text-muted']) ?>
                <?php else : ?>
                    <span class="text-info pull-right">
                        <span class="fa fa-gear fa-spin icon-larger pull-right"></span>
                        <br>
                        <br>
                        <?= $this->CMD('Done', 'setName', ['class' => 'btn btn-sm btn-default']) ?>
                    </span>
                    <strong><?= YY::$ME['NAME'] ?></strong>
                    <br>
                    <?= $this->TXT('You can not change it later', ['class' => 'text-muted']) ?>
                    <br>
                    <?= $this->TXT('Now you can generate another name if you prefer:', ['class' => 'text-muted']) ?>
                    <br>
                    <?= $this->CMD('male', ['regenerateName', 'gender' => 'M']) ?>
                    &nbsp;<span class="text-muted">|</span>&nbsp;
                    <?= $this->CMD('female', ['regenerateName', 'gender' => 'F']) ?>
                    &nbsp;<span class="text-muted">|</span>&nbsp;
                    <?= $this->CMD('random', ['regenerateName', 'gender' => null]) ?>
                <?php endif; ?>
            </div>
            <div class="well clearfix">
                <span><?= $this->TXT('3. Bookmarklet') ?></span>
                <span>
                    <?php if (isset(YY::$ME['nameConfirmed'])) : ?>
                        <span class="fa fa-gear fa-spin icon-larger pull-right"></span>
                        <br>
                        <?= $this->TXT('Your personal access button:', ['class' => 'text-muted']) ?>
                        <br>
                        <script>
                            function onBookmarkletTemplateClick() {
                                alert(<?= json_encode(YY::Translate("Do not click here! Just drag the button onto your bookmarks panel")) ?>);
                                return false;
                            }
                            $('#_YY_<?= YY::GetHandle($this) ?>')
                                .off('click', '.bm-template')
                                .on('click', '.bm-template', onBookmarkletTemplateClick);
                        </script>
                        <div style="text-align: center">
                            <a class="bm-template" href="<?= YY::Config('user')->getBookmarkletScript() ?>">
                                <?= htmlspecialchars(YY::$ME['NAME']) ?>
                            </a>
                        </div>
                        <?= $this->TXT('Drag it onto your bookmarks panel to create new bookmarklet. Then click the created bookmarklet.',
                            ['class' => 'text-muted']) ?>
                    <?php else : ?>
                        <br>
                        <?= $this->TXT('Select nickname first', ['class' => 'text-muted']) ?>
                    <?php endif; ?>
                </span>
            </div>

        </div>
        <?php
    }

    function setLanguage()
    {
        $this['languageConfirmed'] = true;
        $this->regenerateName(['gender' => null]);
    }

    function regenerateName($_params)
    {
        $lang = isset(YY::$CURRENT_VIEW['LANGUAGE']) ? YY::$CURRENT_VIEW['LANGUAGE'] : 'en';
        $gender = $_params['gender'];
        YY::$ME['NAME'] = YY::Config('user')->generateName(['language' => $lang, 'gender' => $gender]);
    }

    function setName()
    {
        YY::$ME['nameConfirmed'] = true;
        YY::$ME['CURRENT_KEY'] = YY::GenerateNewYYID();
    }

    function eventLanguageChanged()
    {
        if (empty(YY::$ME['nameConfirmed'])) {
            $this->setLanguage();
        }
    }

}
