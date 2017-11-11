<?php

$curl = curl_init('https://web/token');

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, [
    'code' => $_GET['code'],
    'redirect_uri' => "https://overlay?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId(),
]);
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
    exit;

}
?>
<html>
<head>
    <title>Overlay</title>
</head>
<body>
<p>User info:</p>
<pre class="user-info"><?= json_encode($json, JSON_PRETTY_PRINT) ?></pre>
</body>
</html>
