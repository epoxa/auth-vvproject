<?php
/**
 */
use YY\System\YY;

header('Content-type: text/javascript; charset=utf-8');
setcookie('auth-' . YY::$ME['PUBLIC_KEY'], substr(YY::$ME['CURRENT_KEY'], 0, 16), time() + 10 * 366 * 24 * 3600, '/', DOMAIN_NAME, true, true);

?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    localStorage.setItem('auth-<?= YY::$ME['PUBLIC_KEY'] ?>', '<?= substr(YY::$ME['CURRENT_KEY'], -16) ?>');
    go();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
