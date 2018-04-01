<?php
/**
 * @var array $params
 */
use YY\System\YY;

ob_start();
YY::DrawEngine('template-wrong-bookmarklet-script.php');
$script = ob_get_clean();

header('Content-type: text/html; charset=utf-8');
$overlay_url = $params['overlay_url'];
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        <?= $script ?>
    </script>
</head>
<body>
</body>
</html>
