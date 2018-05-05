<?php
/**
 * @var array $params
 */
header('Content-type: text/html; charset=utf-8');
$redirect_url = $params['redirect_url'];
$where = $params['where'];
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        parent.postMessage('loading', <?= json_encode($where) ?>);
        location.replace(<?= json_encode($redirect_url) ?>);
    </script>
</head>
<body>
</body>
</html>
