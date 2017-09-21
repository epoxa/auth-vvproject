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

        $redirect_uri = YY::Config('tokens')->createOAuth([
            'user' => YY::$ME,
            'state' => $this['state'],
            'redirect_uri' => $this['redirect_uri'],
        ]);

        if ($redirect_uri) {

            $redirect_uri = json_encode($redirect_uri);
            YY::clientExecute("location.replace($redirect_uri)");

        } else {

            $errorMessage = YY::Translate('Something went wrong sorry');
            $errorMessage = json_encode($errorMessage);
            YY::clientExecute("alert($errorMessage)");
        }
    }

}
