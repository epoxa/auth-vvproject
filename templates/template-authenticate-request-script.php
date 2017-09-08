<?php
/**
 */
use YY\System\YY;

header('Content-type: text/javascript; charset=utf-8');
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    (function() {
        var vvroot = 'https://'.concat('<?= $_SERVER['HTTP_HOST'] ?>');
        var url = vvroot.concat('/?view=authenticate');
        var chld = window.open(url, '_blank', 'left=8000,top=-1000,height=1,width=1,location=no,toolbar=no,directories=no,status=no,menubar=no');
        if (chld) {
            addEventListener('message', function (e) {
                if (e.origin == vvroot) {
                    console.info(e.data);
                    eval(e.data);
                } else {
                    console.warn(e);
                }
            });
        } else {
            alert('Can not authenticate!');
        }
    })();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
