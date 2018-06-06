<?php

use YY\Auth\Main;
use YY\System\Utils;
use YY\System\YY;

$viewId = isset($_GET['view']) ? $_GET['view'] : null;

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Credentials: true');

YY::TryRestore();

if ($viewId === 'boot') {

    // Bookmarklet pressed
    // Must return boot script text or html page depending on "mode" param
    // Incarnation may be absent at the point

    $needUpdateBookmarklet = !isset($_GET['version']) || $_GET['version'] != BOOT_VERSION;
    $isWindowed = isset($_GET['mode']) && $_GET['mode'] === 'window';

    if (
        !isset($_GET['mode'], $_GET['guest'], $_GET['where'], $_GET['title'])
        || !in_array($_GET['mode'], ['inline', '', 'window'])
        || !preg_match('/^[0-9a-f]{32}$/', $_GET['guest'])
        || $_GET['version'] < BOOT_MIN_VERSION
    ) {
        // TODO: Seems we're not ready for translation
        YY::Log('warning', 'Wrong bookmarklet, $_GET=' . print_r($_GET, true));
        $errorMessage = YY::Translate('Your bookmarklet is outdated. Update now?');
        $recoverUrl = 'https://' . ROOT_URL . "recover";
        if (isset($_GET['version'], $_GET['key']) && $_GET['version'] == '4' && preg_match('/^[0-9a-f]{32}$/', $_GET['key'])) {
            $user = YY::Config('user')->loadFromDatabase([
                'CURRENT_KEY' => $_GET['key'],
            ]);
            if ($user) {
                $recoverUrl .= '?public=' . $user['PUBLIC_KEY'] . '&key=' . $_GET['key'];
            }
        } else if (isset($_GET['guest']) && preg_match('/^[0-9a-f]{32}$/', $_GET['guest'])) {
            $recoverUrl .= '?public=' . $_GET['guest'];
        }
        YY::DrawEngine(
            $isWindowed ? 'template-wrong-bookmarklet-window.php' : 'template-wrong-bookmarklet-script.php',
            [
                'errorMessage' => $errorMessage,
                'recoverUrl' => $recoverUrl,
            ]
        );
        exit;
    };


    $site = parse_url($_GET['where'], PHP_URL_HOST);
    $path = parse_url($_GET['where'], PHP_URL_PATH);

    $isOurSite = ($site === $_SERVER['HTTP_HOST']);

    $alreadyAuthorized = isset(YY::$ME, YY::$ME['PUBLIC_KEY']) && YY::$ME['PUBLIC_KEY'] === $_GET['guest'];

    if ($alreadyAuthorized) {

        if ($isOurSite) {

            // On our site we'll install bookmarklet

            assert(!$isWindowed);

            YY::Config('user')->touch([
                'user' => YY::$ME,
            ]);
            YY::Config('user')->saveToDatabase([
                'user' => YY::$ME,
            ]);
            /** @var Main $main */
            $main = YY::$ME['curator'];
            unset($main['creator']);
            $main->setPage('success');
            YY::DrawEngine('template-store-private-key-script.php');

        } else {

            // On foreign sites we can:
            // 1) call default overlay web.vvproject.com for unregistered sites,
            // 2) authenticate user on the site,
            // 3) show site menu,
            // ... etc

            $host = YY::Config('sites')->getHostInfo(['NAME' => $site]);

            if ($host && $host['REDIRECT_URI']) {

                assert(!$isWindowed);

                $redirect_uri = YY::Config('tokens')->createOAuth([
                    'user' => YY::$ME,
                    'state' => 'public',
                    'where' => $_GET['where'],
                    'title' => $_GET['title'],
                    'redirect_uri' => $host['REDIRECT_URI'],
                ]);

                if ($redirect_uri) {

                    $redirect_uri = json_encode($redirect_uri);
                    echo "location.replace($redirect_uri)";

                } else {

                    $errorMessage = YY::Translate('Something went wrong sorry');
                    $errorMessage = json_encode($errorMessage);
                    echo "alert($errorMessage)";
                }

            } else {

                $full_overlay_url = YY::Config('tokens')->createOAuth([
                    'user' => YY::$ME,
                    'state' => 'public',
                    'where' => $_GET['where'],
                    'title' => $_GET['title'],
                    'redirect_uri' => $_SERVER['ENV']['LINKS']['OVERLAY'],
//                    'mode' // TODO
                ]);

                if ($isWindowed) {

                    YY::DrawEngine('template-windowed-margin.php', [
                        'overlay_url' => $full_overlay_url
                    ]);

                } else {

                    $token = YY::Config('tokens')->create([
                        'data' => [
                            'overlay_url' => $full_overlay_url,
                            'guest' => YY::$ME['PUBLIC_KEY'],
                            'where' => $_GET['where'],
                        ],
                    ]);
                    YY::DrawEngine('template-page-margin.php', [
                        'overlay_url' => $full_overlay_url,
                        'redirect_token' => $token,
                    ]);
                }

            }

        }

    } else /* if (Utils::IsSessionValid()) */ {

        if (!YY::$ME) {
            YY::createNewIncarnation();
        } else if (!Utils::IsSessionValid()) {
            Utils::StartSession(YY::$ME->_YYID);
        }

        $_SESSION['auth_guest'] = $_GET['guest'];
        $_SESSION['auth_challenge'] = YY::GenerateNewYYID();
        $_SESSION['original_request'] = $_SERVER['REQUEST_URI'];
        $_SESSION['auth_where'] = $_GET['where'];

        if ($isWindowed) {

            header("Location: /?view=authenticate&mode=window");
            exit;

        } else {

            YY::DrawEngine('template-authenticate-request-script.php');

        }

    }

} else if ($viewId === 'authenticate') {

    $isWindowed = isset($_GET['mode']) && $_GET['mode'] === 'window';

    if (Utils::IsSessionValid() && isset($_SESSION['auth_guest'], $_SESSION['auth_challenge']) && preg_match('/^[0-9a-f]{32}$/', $_SESSION['auth_guest'])) {

        if (isset($_GET['secret']) && preg_match('/^[0-9a-f]{32}$/', $_GET['secret'])) {

            $guest = YY::Config('user')->loadFromDatabase([
                'PUBLIC_KEY' => $_SESSION['auth_guest'],
            ]);
            $authIndex = 'auth-' . $_SESSION['auth_guest'];
            $loginOk
                = isset($guest, $_COOKIE[$authIndex])
                && $_GET['secret'] === md5($_SESSION['auth_challenge'] . substr($guest['CURRENT_KEY'], -16))
                && $_COOKIE[$authIndex] === substr($guest['CURRENT_KEY'], 0, 16);
//            $where = $_SESSION['auth_where'];
            if ($loginOk) {
                YY::$ME = $guest;
                YY::$ME->_REF;
                Utils::UpdateSession(YY::$ME->_YYID);
                $isOurSite = (parse_url($_SESSION['auth_where'], PHP_URL_HOST) === $_SERVER['HTTP_HOST']);
                if ($isOurSite) {
                    /** @var Main $main */
                    $main = YY::Config('user')->getMainCurator();
                    $main->setPage('success');
                    $script = "location.reload();";
                } else if ($isWindowed) {
                    header("Location: $_SESSION[original_request]");
                    exit;
                } else {
                    $script = YY::Config('user')->getBookmarkletScript();
                }
            } else {
                $recoverUrl = "https://$_SERVER[HTTP_HOST]/recover";
                if ($guest) {
                    $lang = isset($guest['LANGUAGE'])?$guest['LANGUAGE']:'';
                    $recoverUrl .= "?public=$guest[PUBLIC_KEY]&lang=$lang";
                }
                ob_start();
                YY::Log('warning', 'Authentication failed, $_GET=' . print_r($_GET, true));
                YY::DrawEngine(
                    'template-wrong-bookmarklet-script.php',
                    [
                        'errorMessage' => YY::Translate('Your bookmarklet is outdated. Update now?'),
                        'recoverUrl' => $recoverUrl,
                    ]
                );
                $script = ob_get_clean();
//                $script = "location=" . json_encode($recoverUrl) . ";";
            }
            if ($isWindowed) {
                YY::Log('warning', 'Authentication failed, $_GET=' . print_r($_GET, true));
                YY::DrawEngine(
                    'template-wrong-bookmarklet-window.php',
                    [
                        'errorMessage' => YY::Translate('Your bookmarklet is outdated. Update now?'),
                        'recoverUrl' => $recoverUrl,
                    ]
                );
            } else {
                YY::DrawEngine('template-boot-proxy.php', [
                    'script' => $script,
                    'where' => $_SESSION['auth_where'],
                ]);
            }
            unset($_SESSION['auth_guest'], $_SESSION['auth_challenge'], $_SESSION['original_request'], $_SESSION['auth_where']);

        } else {

            YY::DrawEngine('template-authenticate-window.php');

        }


    } else {

        echo "Something went wrong. SESSION_ID: " . session_id();

    }

} else if ($viewId === 'proxy') {

    ob_start();
    YY::DrawEngine('template-page-margin.php', ['overlay_url' => ROOT_URL . '?']);
    $script = ob_get_clean();
    YY::DrawEngine('template-boot-proxy.php', [
        'script' => $script,
        'where' => $_GET['where'],
    ]);
} else if ($viewId === 'loader') {

    $data = YY::Config('tokens')->utilize([
        'token' => $_GET['token'],
    ]);

    if (isset($data, $data['guest'], $data['overlay_url'], $data['where']) && $data['guest'] === YY::$ME['PUBLIC_KEY']) {
        YY::DrawEngine('template-iframe-loader.php', [
            'redirect_url' => $data['overlay_url'],
            'where' => $data['where'],
        ]);
    }

} else if (isset($_GET['where'])) {

    echo "<pre>";
    print_r($_GET);
    echo "</pre>";

} else if (isset(YY::$ME)) {

    Utils::StoreParamsInSession();
    if ($_SERVER['QUERY_STRING'] || !in_array($_SERVER['REQUEST_URI'], ['', '/'])) {
        Utils::RedirectRoot();
    }
    YY::DrawEngine("template-engine.php");

} else {

    $ready = isset($_COOKIE[INSTALL_COOKIE_NAME]) && Utils::CheckTempKey($_COOKIE[INSTALL_COOKIE_NAME]);
    if (isset($_COOKIE[INSTALL_COOKIE_NAME])) {
        setcookie(INSTALL_COOKIE_NAME, "", time() - 3600);
        unset($_COOKIE[INSTALL_COOKIE_NAME]);
    }
    if ($ready) {
        YY::Log('system', 'Requirements checkup is ok');
        YY::createNewIncarnation();
        Utils::StartSession(YY::$ME->_YYID);
        Utils::StoreParamsInSession();
        Utils::RedirectRoot();
    } else {
        YY::Log('system', 'Draw requirements checkup');
        include TEMPLATES_DIR . 'template-checkup.php';
    }

}

