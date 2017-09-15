<?php

use YY\Auth\Aim;
use YY\Auth\Main;
use YY\Core\Cache;
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

    // TODO: Handle wrong bookmarklet version

    if (
        !isset($_GET['mode'], $_GET['guest'], $_GET['where'], $_GET['title'])
        || !in_array($_GET['mode'], ['inline', 'window'])
        || !preg_match('/^[0-9a-f]{32}$/', $_GET['guest'])
    ) {
        // Wrong boot request
        http_response_code(400);
        exit;
    };

    $isWindowed = $_GET['mode'] === 'window';


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
            // 1) call default vvproject for unregistered sites,
            // 2) authenticate user on the site,
            // 3) show site menu,
            // ... etc

            $host = YY::Config('sites')->getHostInfo(['NAME' => $site]);

            if ($host && $host['REDIRECT_URI']) {

                assert(!$isWindowed);

                $code = YY::GenerateNewYYID();

                $url = parse_url($host['REDIRECT_URI']);
                $url = ((isset($url["scheme"])) ? $url["scheme"] . "://" : "https://")
//                    . ((isset($url["user"])) ? $url["user"]
//                        . ((isset($url["pass"])) ? ":" . $url["pass"] : "") . "@" : "")
                    . ((isset($url["host"])) ? $url["host"] : "auth.vvproject.com")
                    . ((isset($url["port"])) ? ":" . $url["port"] : "")
                    . (isset($url['path']) ? $url['path'] : '')
                    . '?' . (isset($url['query']) ? $url['query'] . '&' : '') . "code=$code&state=public";

                $user_info = [
                    'public_key' => YY::$ME['PUBLIC_KEY'],
                    'name' => YY::$ME['NAME'],
                    'language' => isset(YY::$ME['LANGUAGE']) ? YY::$ME['LANGUAGE'] : null,
                    'age' => floor((time() - YY::$ME['CAME_DATE']) / (24 * 3600)),
                    'active_days' => YY::$ME['ACTIVE_DAYS'],
                    'redirect_uri' => $host['REDIRECT_URI'],
                    'debug' => $url,
                ];


                if (!file_exists(TOKENS_DIR)) {
                    mkdir(TOKENS_DIR, 0777, true);
                }
                $fileName = TOKENS_DIR . $code;
                file_put_contents($fileName, json_encode($user_info));

                $url = json_encode($url);
                echo "location.replace($url)";

            } else {

                echo "alert('Hey, here is another page');";

            }

        }

    } else /* if (Utils::IsSessionValid()) */ {


        if ($isWindowed) {

            echo "Unimplemented boot case :(";

        } else {

            if (!YY::$ME) {
                YY::createNewIncarnation(true);
            } else if (!Utils::IsSessionValid()) {
                Utils::StartSession(YY::$ME->_YYID);
            }

            $_SESSION['auth_guest'] = $_GET['guest'];
            $_SESSION['auth_challenge'] = YY::GenerateNewYYID();
            $_SESSION['original_request'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_where'] = $_GET['where'];
//            echo "alert('SESSION_ID = " . session_id() . "');";
//            echo "alert(" . json_encode(print_r($_SESSION, true)) . ");";
            YY::DrawEngine('template-authenticate-request-script.php');

        }

    }

} else if ($viewId === 'authenticate') {

    if (Utils::IsSessionValid() && isset($_SESSION['auth_guest'], $_SESSION['auth_challenge']) && preg_match('/^[0-9a-f]{32}$/', $_SESSION['auth_guest'])) {

        if (isset($_GET['secret']) && preg_match('/^[0-9a-f]{32}$/', $_GET['secret'])) {

            $guest = YY::Config('user')->loadFromDatabase([
                'PUBLIC_KEY' => $_SESSION['auth_guest'],
            ]);
            $loginOk = $guest && $_GET['secret'] === md5($_SESSION['auth_challenge'] . $guest['CURRENT_KEY']);
            $where = $_SESSION['auth_where'];
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
                } else {
                    $script = YY::Config('user')->getBookmarkletScript();
                }
            } else {
                $main = YY::Config('user')->getMainCurator();
                $main->setPage('recover');
                unset($_SESSION['auth_guest'], $_SESSION['auth_challenge']);
                $script = "location='https://$_SERVER[HTTP_HOST]/?lang=$guest[LANGUAGE]';";
            }
            YY::DrawEngine('template-boot-proxy.php', [
                'script' => $script,
                'where' => $_SESSION['auth_where'],
            ]);
            unset($_SESSION['auth_guest'], $_SESSION['auth_challenge'], $_SESSION['original_request'], $_SESSION['auth_where']);

        } else {

            YY::DrawEngine('template-authenticate-window.php');

        }


    } else {

        echo "SESSION_ID: " . session_id();

    }

} else if ($viewId === 'proxy') {

    ob_start();
    YY::DrawEngine('template-page-margin.php', ['protocol' => '']);
    $script = ob_get_clean();
    YY::DrawEngine('template-boot-proxy.php', [
        'script' => $script,
        'where' => $_GET['where'],
    ]);

} else if (isset($_GET['where'])) {

    echo "<pre>";
    print_r($_GET);
    echo "</pre>";

} else if (isset(YY::$ME)) {

    Utils::StoreParamsInSession();
    if ($_SERVER['QUERY_STRING']) {
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

