<?php


namespace YY\Auth;


use YY\Core\Data;
use YY\System\YY;

/**
 * Class Aim
 *
 * @package YY\Auth
 *
 * @property string $type
 * @property string $client_id
 * @property string $scope
 * @property string $redirect_uri
 * @property string $state
 */
class Aim extends Data
{

    public function oauth()
    {
        // TODO: Ensure user installed bookmarklet
        // TODO: Check user's preference if to ask user every time when new application trying to identify visitor

        $code = YY::GenerateNewYYID();

        $parse_url = parse_url($this['redirect_uri']);
        $parse_url["query"] = (isset($parse_url["query"]) ? $parse_url["query"] . '&' : '') . "code=$code&state=" . $this['state'];

        $redirect_uri =
            ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
            . ((isset($parse_url["user"])) ? $parse_url["user"]
                . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
            . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
            . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
            . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
            . ("?" . $parse_url["query"])
            . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "")
            ;
        $redirect_uri = json_encode($redirect_uri);

        $user_info = [
            'public_key' => YY::$ME['PUBLIC_KEY'],
            'name' => YY::$ME['NAME'],
            'language' => isset(YY::$ME['LANGUAGE']) ? YY::$ME['LANGUAGE'] : null,
            'age' => floor((time() - YY::$ME['CAME_DATE']) / (24 * 3600)),
            'active_days' => YY::$ME['ACTIVE_DAYS'],
            'redirect_uri' => $this['redirect_uri'],
        ];


        if (!file_exists(TOKENS_DIR)) {
            mkdir(TOKENS_DIR, 0777, true);
        }
        $fileName = TOKENS_DIR . $code;
        file_put_contents($fileName, json_encode($user_info));

        YY::clientExecute("location.replace($redirect_uri)");
    }

}
