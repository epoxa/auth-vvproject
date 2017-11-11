<?php

const AUTH_VVPROJECT_HOST = 'https://web'; // This is container host name in docker

session_start();

$redirect_uri =
    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
    $_SERVER['HTTP_HOST'] . '/login.php';

if (empty($_GET['code'])) {

    // Initiate external authentication

    $_SESSION['state'] = uniqid();

    /*
     *  All these are not required:
     *
     *  client_id = public
     *  scope = public
     *  response_type = code
     *  approval_prompt = auto
     *
     */
    $authorization_endpoint = AUTH_VVPROJECT_HOST . '/authorize/?state=' . $_SESSION['state'] . '&redirect_uri=' . urlencode($redirect_uri);

    header('Location: ' . $authorization_endpoint);

} else {


    $state = $_SESSION['state'];
    unset($_SESSION['state']);

    if (!((!$state && $_GET['state'] === 'public') || ($_GET['state'] === $state))) { // Ensure state param is correct (or public)

        echo "<p>Login failed: <span class='error'>invalid state</span>. Expected state [$state] but received [$_GET[state]].</p>";
        unset($state);
        echo "<p><a href='login.php'>Retry</a></p>";
        echo "<p><a href='index.php'>Cancel</a></p>";

    } else {

        // Exchange access code to user details

        $token_endpoint = AUTH_VVPROJECT_HOST . '/token';
        $curl = curl_init($token_endpoint);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'code' => $_GET['code'],
            'redirect_uri' => $redirect_uri,
        ]);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Cookie: PHPUNIT_SELENIUM_TEST_ID=$_COOKIE[PHPUNIT_SELENIUM_TEST_ID]"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = $json = $error = null;
        try {
            $result = curl_exec($curl);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        if (!$error) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                $error = 'Wrong HTTP code: ' . $httpCode;
                if ($result) {
                    $error .= '. Server returned: ' . $result;
                }
            } else {
                $json = json_decode($result, true);
                if (!$json) {
                    $error = 'Invalid JSON answer: ' . $result;
                } else if (isset($json['error'])) {
                    $error = isset($json['error_description']) ? $json['error_description'] : $json['error'];
                } else if (!isset($json['name'], $json['public_key'])) {
                    $error = 'Invalid JSON answer: ' . $result;
                }
            }
        }
        if ($error) {

            $error = htmlspecialchars($error);
            echo "<p>Auth code exchange failed: <span class='error'>$error</span>.</p>";
            echo "<p><a href='login.php'>Retry</a></p>";
            echo "<p><a href='index.php'>Cancel</a></p>";

        } else {

            $_SESSION['userName'] = $json['name'];
            $_SESSION['publicKey'] = $json['public_key'];
            header('Location: index.php');

        }
    }

}
