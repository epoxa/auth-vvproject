<?php
/**
 */
use YY\System\YY;

header('Content-type: text/javascript; charset=utf-8');
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    localStorage.setItem('auth-<?= YY::$ME['PUBLIC_KEY'] ?>', '<?= YY::$ME['CURRENT_KEY'] ?>');
    go();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
