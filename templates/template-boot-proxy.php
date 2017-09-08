<?php
use YY\System\YY;
/** @var array $params */
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="Robots" content="noindex,nofollow"/>
  <title><?= YY::Config('title') ?></title>
  <link rel="icon" href="<?= PROTOCOL . ROOT_URL ?>favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="<?= PROTOCOL . ROOT_URL ?>favicon.ico" type="image/x-icon">
</head>
<body style="margin: 0">
<script type="text/javascript">
    opener.postMessage(<?= json_encode($params['script']) ?>, <?= json_encode($params['where'])?>);
    close();
</script>
</html>
