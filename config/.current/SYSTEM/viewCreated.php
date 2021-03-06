<?php

use YY\Auth\Aim;
use YY\Auth\Main;
use YY\Auth\Recover;
use YY\Develop\Assistant;
use YY\Translation\Editor;
use YY\Translation\LanguageSelector;
use YY\Translation\Agent;
use YY\System\YY;

if (isset(YY::$CURRENT_VIEW['request']['build'])) {

    $builder = null;

    if (isset(YY::$ME['builder'])) {

        $builder = YY::$ME['builder'];

    } else {

        $builder = new Assistant(null);
        YY::$ME['builder'] = $builder;

    }

    YY::$CURRENT_VIEW['ROBOT'] = $builder;

} else if (
    isset(YY::$CURRENT_VIEW['request']['translate']) && YY::$CURRENT_VIEW['request']['translate'] == 'on'
    ||
    empty(YY::$CURRENT_VIEW['request']['translate']) && isset(YY::$ME['mode']) && YY::$ME['mode'] == 'translate'
) {

    if (isset(YY::$CURRENT_VIEW['request']['translate']))  YY::$ME['mode'] = 'translate';

    $editor = new Editor();
    $editor->open();

} else {

    if (isset(YY::$CURRENT_VIEW['request']['translate']) && YY::$CURRENT_VIEW['request']['translate'] == 'off')
    {
        YY::$ME['mode'] = 'use';
    }

    if (isset(YY::$CURRENT_VIEW['pathInfo']) && preg_match('#^/authorize/?\?#', YY::$CURRENT_VIEW['pathInfo'])) {

        if (isset(YY::$CURRENT_VIEW['request']['client_id'])) {
            $client_id = YY::$CURRENT_VIEW['request']['client_id'];
        } else {
            $client_id = 'public';
        }

        if (isset(YY::$CURRENT_VIEW['request']['scope'])) {
            $scope = YY::$CURRENT_VIEW['request']['scope'];
        } else {
            $scope = 'public';
        }

        // Using web-session instead of incarnation allows to change character transparently

        $_SESSION['aim'] = new Aim([
            'type' => 'oauth',
            'client_id' => $client_id,
            'scope' => $scope,
            'where' => null,
            'title' => null,
            'redirect_uri' => YY::$CURRENT_VIEW['request']['redirect_uri'],
            'state' => YY::$CURRENT_VIEW['request']['state'],
        ]);

    }

    /** @var Main $curator */
    $curator = YY::Config('user')->getMainCurator();

    YY::$CURRENT_VIEW['ROBOT'] = $curator;

    // Select translation language

    if (isset(YY::$CURRENT_VIEW->request['lang'])) {
        $lang = YY::$CURRENT_VIEW->request['lang'];
    } else if (isset(YY::$ME['LANGUAGE'])) {
        $lang = YY::$ME['LANGUAGE'];
    } else {
        $lang = null;
    }
    if ($lang) {
        $all = YY::Config('LANGUAGES');
        if (isset($all[$lang])) {
            YY::$CURRENT_VIEW['LANGUAGE'] = $lang;
            YY::$CURRENT_VIEW['TRANSLATION'] = $all[$lang];
            if (isset(YY::$ME['LANGUAGE']) && YY::$ME['LANGUAGE'] != $lang) {
                unset(YY::$ME['LANGUAGE']);
            }
        } else {
            unset(YY::$ME['LANGUAGE']);
            $lang = null;
        }
    }

    $languageSelector = $curator['languageSelector'];

    if (empty(YY::$CURRENT_VIEW['LANGUAGE'])) {
        $lang = LanguageSelector::TryAutoDetectLanguage();
        if ($lang) {
            YY::$CURRENT_VIEW['LANGUAGE'] = $lang;
        }
        if (empty(YY::$ME['LANGUAGE'])) {
            $myHandle = '_YY_' . YY::GetHandle($languageSelector);
            YY::clientExecute("$('#$myHandle').find('.dropdown-toggle').click()");
        }
    }

    if (isset(YY::$ME['translateMode'])) {
        YY::$CURRENT_VIEW['TRANSLATOR'] = new Agent();
    }

    if (preg_match('#^/recover(/|\?|$)#', YY::$CURRENT_VIEW['pathInfo'])) {
        /** @var Recover $recover */
        $recover = $curator->setPage('recover');
        $public_key = isset(YY::$CURRENT_VIEW['request']['public']) ? YY::$CURRENT_VIEW['request']['public'] : null;
        $private_key = isset(YY::$CURRENT_VIEW['request']['key']) ? YY::$CURRENT_VIEW['request']['key'] : null;
        $recover->init($public_key, $private_key);
    }

}

