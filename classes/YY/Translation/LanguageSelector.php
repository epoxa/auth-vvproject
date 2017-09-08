<?php


namespace YY\Translation;

use YY\System\YY;
use YY\System\Robot;

class LanguageSelector extends Robot
{

    static private $languages = [
        'EN' => 'English',
        'RU' => 'Русский',
    ];

    static function TryAutoDetectLanguage()
    {
        $lang = 'English';
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $al = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($al as $acceptable) {
                foreach(self::$languages as $code => $name) {
                    if (preg_match("/^$code/i", $acceptable)) {
                        $lang = $name;
                        break 2;
                    }
                }
            }
        }
        if (empty(YY::Config('LANGUAGES')[$lang])) {
            $lang = null;
        }
        return $lang;
    }

    function __construct($init)
    {
        parent::__construct($init);

        $this['newLangName'] = '';

        $this['attributes'] = [
            'class' => 'language-selector btn-group',
        ];

    }


    protected function _PAINT()
    {
        ?>
        <?php if (count($langList = YY::Config('LANGUAGES'))) : ?>
            <?php if (isset(YY::$ME['LANGUAGE'])) : ?>
                <?php
                $lang = YY::$ME['LANGUAGE'];
                $translation = YY::Config("LANGUAGES.$lang");
                $langTitle = isset($translation['']) ? $translation[''] : $lang;
                ?>
                <button type="button" class="btn btn-xs <?= isset(YY::$ME['translateMode']) ? 'btn-danger' : 'btn-link' ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php if (isset(YY::$CURRENT_VIEW['LANGUAGE'])) : ?>
                        <span class="">
                            <?= htmlspecialchars($langTitle) ?> <span class="caret"></span>
                        </span>
                    <?php else: ?>
                        <span class="fa-3x fa fa-globe">
                    <?php endif; ?>
                </button>
            <?php else: ?>
                <?php if (isset(YY::$CURRENT_VIEW['LANGUAGE'])) : ?>
                    <?php $langTitle = isset(YY::$CURRENT_VIEW['TRANSLATION']['']) ? YY::$CURRENT_VIEW['TRANSLATION'][''] : YY::$CURRENT_VIEW['LANGUAGE']; ?>
                    <?=
                    $this->CMD
                    (
                        ['' => htmlspecialchars($langTitle) . ' <i class="fa fa-arrow-right"></i>'],
                        ['switchTo', 'language' => YY::$CURRENT_VIEW['LANGUAGE']],
                        [
                            'class' => 'btn btn-lg btn-success',
                            'style' => 'margin-top: 10px'
                        ]
                    )
                    ?>
                <?php endif; ?>
                <button type="button" class="btn btn-lg btn-warning dropdown-toggle" style="margin: 10px 10px 0 0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-lg fa-globe"></i>
                </button>
            <?php endif; ?>
            <ul class="dropdown-menu dropdown-menu-right">
                <?php foreach ($langList as $lang => $translation) : ?>
                    <?php if ($lang) : ?>
                        <li>
                            <?=
                            $this->CMD(
                                ['' => (isset(YY::$ME['LANGUAGE']) && YY::$ME['LANGUAGE'] == $lang ? '<i class="fa fa-lg fa-check pull-right"></i>' : '')
                                    . (isset($translation['']) ? htmlspecialchars($translation['']) : $lang) ],
                                ['switchTo', 'language' => $lang]
                            )
                            ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li role="separator" class="divider"></li>
                <?php if (isset(YY::$ME['translateMode'])) : ?>
                    <?= $this->CMD(['' => '<span class="fa fa-fw fa-close pull-right">'], 'toggleTranslateMode', ['class' => 'inline', 'style' => "color: black"]) ?>
                    <li class="disabled inline" style="padding-left: 20px">
                        <?= $this->TXT('Translate mode') ?>
                    </li>
                    <li class="inline-menu-container">
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-plus">'], 'newLanguageInit') ?>
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-edit">'], 'editLanguage') ?>
                        <?php if (isset(YY::$CURRENT_VIEW['LANGUAGE'])) : ?>
                            <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-trash">'], 'deleteLanguage',
                                ['confirm' => YY::Translate(["Are you sure to totally delete language %s?", YY::$CURRENT_VIEW['LANGUAGE']])]) ?>
                        <?php endif; ?>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li class="inline-menu-container">
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-user-times pull-right">'], 'reboot') ?>
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-wrench pull-right">'], 'build') ?>
                    </li>
                <?php elseif (isset(YY::$ME['LANGUAGE'])) : ?>
                    <li><?= $this->CMD('Translate mode', 'toggleTranslateMode', ['class' => 'text-muted']) ?></li>
                    <li role="separator" class="divider"></li>
                    <li class="inline-menu-container">
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-user-times pull-right">'], 'reboot') ?>
                        <?= $this->CMD(['' => '<span class="fa fa-lg fa-fw fa-wrench pull-right">'], 'build') ?>
                    </li>
                <?php else : ?>
                    <li><?= $this->CMD('Add your language', 'newLanguageInit', ['class' => 'text-muted']) ?></li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <?= $this->CMD(['' => '<span class="fa-3x fa fa-globe"></span>'], 'newLanguageInit', ['class' => "btn btn-xs btn-link"]) ?>
        <?php endif; ?>

        <div class="modal" id="newLangDialog" tabindex="-1" role="dialog" aria-labelledby="newLangDialogLabel">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="newLangDialogLabel"><?= $this->TXT('Create Translation') ?></h4>
                    </div>
                    <div class="modal-body">
                        <form onsubmit="$('#newLangDialog').find('.modal-content .btn-primary').click(); return false;">
                            <div class="form-group">
                                <label for="<?= YY::GetHandle($this) ?>[newLangName]"><?= $this->TXT('Language Name') ?></label>
                                <?= $this->INPUT('newLangName',
                                    [
                                        'placeholder' => "Русский",
                                        'class' => 'form-control'
                                    ]) ?>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <?= $this->CMD('Save', 'saveNewLanguage', ['class' => "btn btn-primary", 'data-dismiss' => "modal" ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    public function openLangMenu()
    {
        $myHandle = '_YY_' . YY::GetHandle($this);
        YY::clientExecute("with ($('#$myHandle').find('.dropdown-toggle')) if (attr('aria-expanded') == 'false') click();");
    }

    function toggleTranslateMode()
    {
        if (isset(YY::$ME['translateMode'])) {
            unset(YY::$ME['translateMode']);
            if (isset(YY::$CURRENT_VIEW['TRANSLATOR'])) {
                YY::$CURRENT_VIEW['TRANSLATOR']->close();
            }
            unset(YY::$CURRENT_VIEW['TRANSLATOR'], YY::$ME['translateMode']);
        } else {
            YY::$ME['translateMode'] = true;
            YY::$CURRENT_VIEW['TRANSLATOR'] = new Agent();
            $this->openLangMenu();
        }
    }

    function newLanguageInit()
    {
        $this['newLangName'] = '';
        YY::clientExecute("$('#newLangDialog').modal()");
        $this->focusInput('newLangName');
    }

    function saveNewLanguage()
    {
        $lang = mb_convert_case(trim($this['newLangName']), MB_CASE_TITLE);
        $this['newLangName'] = '';
        YY::clientExecute("$('#newLangDialog').modal('hide')");
        if (!$lang) return;
        $allLanguages = YY::Config('LANGUAGES');
        if (empty($allLanguages[$lang])) {
            $allLanguages[$lang] = [];
            $allLanguages[$lang][''] = $lang;
        }
        $this->switchTo(['language' => $lang]);
        if (empty(YY::$ME['translateMode'])) {
            $this->toggleTranslateMode();
        }
    }

    function switchTo($_params)
    {
        $lang = $_params['language'];
        YY::$ME['LANGUAGE'] = $lang;
        if (isset(YY::$ME['ID']) && YY::$ME['ID']) {
            YY::Config('user')->saveToDatabase([
                'user' => YY::$ME,
            ]);
        }
        YY::$CURRENT_VIEW['LANGUAGE'] = $lang;
        YY::$CURRENT_VIEW['TRANSLATION'] = YY::Config('LANGUAGES')[$lang];
        if (isset($this['callback'])) {
            $obj = $this['callback']['robot'];
            if ($obj) {
                $methodName = $this['callback']['method'];
                $obj->$methodName();
            }
        }
    }

    function editLanguage()
    {
        YY::redirectUrl('?translate=on');
    }

    function deleteLanguage()
    {
        $lang = isset(YY::$CURRENT_VIEW['LANGUAGE']) ? YY::$CURRENT_VIEW['LANGUAGE'] : null;
        $allLanguages = YY::Config('LANGUAGES');
        unset($allLanguages[$lang]);
        unset(YY::$CURRENT_VIEW['LANGUAGE']);
        unset(YY::$ME['LANGUAGE']);
        $this->openLangMenu();
    }

    function reboot()
    {
        $url = PROTOCOL . ROOT_URL . (YY::$CURRENT_VIEW['queryString'] ? '?' . YY::$CURRENT_VIEW['queryString'] : '');
        YY::$ME->_delete();
        YY::redirectUrl($url);
    }

    function build()
    {
        YY::redirectUrl('?build');
    }

}
