<?php use YY\System\YY; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="Robots" content="noindex,nofollow"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <title><?= YY::Config('title') ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/spacelab.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/common.css">
    <script ok="1" type="text/javascript">
        var rootUrl = '<?= PROTOCOL . ROOT_URL ?>';
        var viewId = '<?= YY::GenerateNewYYID() ?>';
    </script>
    <script ok="1" type="text/javascript" src="/js/engine.js"></script>
    <script ok="1" type="text/javascript" src="/js/jquery-3.2.0.min.js"></script>
    <script ok="1" type="text/javascript" src="/js/bootstrap.min.js"></script>
</head>
<body>
<div id="blind" style="position: fixed; width: 100%; height: 100%; z-index: 2147483647; cursor:progress; display: none">
</div>
</body>
</html>
