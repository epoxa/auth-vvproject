<?php
/**
 * @var array $params
 */
header('Content-type: text/html; charset=utf-8');
$overlay_url = $params['overlay_url'];
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        location.replace(<?= json_encode($overlay_url) ?>);
    </script>
</head>
<body>
</body>
</html>
