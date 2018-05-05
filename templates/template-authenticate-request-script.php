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
        var url = 'https://'.concat('<?= $_SERVER['HTTP_HOST'] ?>','/?view=authenticate&mode=inline');
        var chld = window.open(url, '_blank', 'left=8000,top=-1000,height=1,width=1,location=no,toolbar=no,directories=no,status=no,menubar=no');
        if (chld) {
            window.VVProject = window.VVProject || {};
            var $ = window.VVProject.auth = window.VVProject.auth || {};
            if ($.overlayWindowListener) {
                // TODO: Create one universal listener for all cases
                console.trace('Remove old overlay window listener');
                removeEventListener('message', $.overlayWindowListener)
            }
            $.overlayWindowListener = function (e) {
                if (e.source == chld) {
                    console.info(e.data);
                    eval(e.data);
                } else {
                    console.warn(e);
                }
            };
            addEventListener('message', $.overlayWindowListener);
        } else {
            alert('Can not authenticate!');
        }
    })();

    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
