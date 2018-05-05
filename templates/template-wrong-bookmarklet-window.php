<?php
/**
 * @var array $params
 */
$message = $params['errorMessage'];
$url = $params['recoverUrl'];
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        function goRecover() {
            window.open(<?= json_encode($url) ?>);
            close();
        }
    </script>
</head>
<body>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="javascript:void(0);" onclick="goRecover()">Update</a>
</body>
</html>
