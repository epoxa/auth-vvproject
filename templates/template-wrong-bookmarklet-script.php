<?php
/**
 * @var array $params
 */
use YY\System\YY;

header('Content-type: text/javascript; charset=utf-8');
$overlay_url = $params['overlay_url'];
$message = YY::Translate('Your bookmarklet is outdated. Update now?');
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    (function () {
        if (confirm(<?= json_encode($message) ?>)) {
            window.open(<?= json_encode('https://' . ROOT_URL) ?>);
        }
        if (opener) close();
    })();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
