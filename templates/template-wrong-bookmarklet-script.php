<?php
/**
 * @var array $params
 */

header('Content-type: text/javascript; charset=utf-8');
$message = $params['errorMessage'];
$url = $params['recoverUrl'];
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    (function () {
        if (confirm(<?= json_encode($message) ?>)) {
            location.href = <?= json_encode($url) ?>;
        }
    })();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
