<?php


namespace YY\Translation;


use YY\Core\Exporter;
use YY\System\YY;
use YY\System\Robot;

class Editor extends Robot
{
    public function __construct($init = null)
    {
        parent::__construct($init);
        $this->includeAsset([
            '<link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>',
            '<script src="/js/datatables.js"></script>',
            '<script src="/js/jquery.editable.js"></script>',
            '<script src="/translate/language-editor.js"></script>',
        ]);
    }

    function open()
    {
        YY::$CURRENT_VIEW['ROBOT'] = $this; // I'm ephemeral robot belong to my view
        $phrases = [];
        foreach (YY::Config('LANGUAGES') as $lang => $translation) {
            foreach ($translation as $slug => $phrase) {
                if (!$slug) continue;
                $phrases[$slug][$lang] = $phrase;
            }
        }
        if (isset(YY::$ME['LANGUAGE'])) {
            $lang = YY::$ME['LANGUAGE'];
            uasort($phrases, function ($tr1, $tr2) use ($lang) {
                if (empty($tr1[$lang]) && empty($tr2[$lang])) {
                    return 0;
                } elseif (empty($tr1[$lang])) {
                    return -1;
                } elseif (empty($tr2[$lang])) {
                    return 1;
                } else {
                    return $tr1[$lang] < $tr2[$lang] ? -1 : 1;
                }
            });
        }
        $this['phrases'] = $phrases;
    }

    function _PAINT()
    {
        ?>
        <div class="container-fluid">
            <?php if (isset(YY::$ME['LANGUAGE'])) : ?>
                <? $lang = YY::$ME['LANGUAGE']; ?>
                <?= $this->CMD('Close', 'close', ['class' => 'btn btn-default pull-right']) ?>
                <?= $this->CMD('Save', 'save', ['class' => 'btn btn-default pull-right']) ?>
                <h2>
                    <?= $this->TXT('Translation') ?>
                </h2>
                <table id="list" class="display" width="100%"></table>
                <!--                <table class="table">-->
                <!--                    <tr>-->
                <!--                        <th>--><? //= $this->TXT('Slug') ?><!--</th>-->
                <!--                        <th>--><? //= $this->TXT('Original') ?><!--</th>-->
                <!--                        <th>--><? //= htmlspecialchars($lang) ?><!--</th>-->
                <!--                    </tr>-->
                <!--                    --><?php //foreach ($this['phrases'] as $slug => $phrase) : ?>
                <!--                        <tr>-->
                <!--                            <td>--><? //= htmlspecialchars($slug) ?><!--</td>-->
                <!--                            <td>--><? //= htmlspecialchars($phrase['']) ?><!--</td>-->
                <!--                            <td>--><? //= htmlspecialchars($phrase[$lang]) ?><!--</td>-->
                <!--                        </tr>-->
                <!--                    --><?php //endforeach; ?>
                <!--                </table>-->
            <?php else : ?>
            <?php endif; ?>
        </div>
        <?php
        $data = json_encode($this->getData());
        $myHtmlId = YY::GetHandle($this);
        YY::clientExecute("initLanguageEditor('$myHtmlId', $data)");
    }

    function save()
    {
        Exporter::exportSubtree(YY::Config('LANGUAGES'), CONFIGS_DIR . '/.current/CONFIG/LANGUAGES', ['']);
    }

    function close()
    {
        YY::redirectUrl('?translate=off');
//        if (isset($this['previousRobot'])) {
//            YY::$CURRENT_VIEW['ROBOT'] = $this['previousRobot'];
//        }
    }

    private function getData()
    {
        $lang = isset(YY::$ME['LANGUAGE']) ? YY::$ME['LANGUAGE'] : '';
        $data = [];
        foreach ($this['phrases'] as $slug => $phrase) {
            $data[] = [$slug, isset($phrase['']) ? $phrase[''] : '', isset($phrase[$lang]) ? $phrase[$lang] : ''];
        }
        return $data;
    }

    public function ajaxSetTranslation($_params)
    {
        $slug = $_params['slug'];
        $value = $_params['value'];

        YY::TryRestore();

        if (isset(YY::$ME, YY::$ME['LANGUAGE'])) {
            $lang = YY::$ME['LANGUAGE'];
            $translation = YY::Config('LANGUAGES')[$lang];
            $translation[$slug] = $value;
            $this->save();
        }
    }

}
