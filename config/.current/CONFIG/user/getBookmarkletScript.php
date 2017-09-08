<?php

use YY\System\YY;

// TODO: Maybe it's make sense to build dynamic script (depending on browser type or geolocation for example)

ob_start()
?>

<?php ob_start(); ?>
    <script>
        <?php ob_end_clean(); ?>

        <?php /* START SCRIPT */ ?>
        (function () {
            var u = 'https://<?= $_SERVER['HTTP_HOST'] ?>/?view=boot&version=<?= BOOT_VERSION ?>&guest=<?= YY::$ME['PUBLIC_KEY'] ?>&where='.concat(encodeURIComponent(location.toString()),'&title=',encodeURIComponent(document.title));
            try {
                eval('console.info(\'eval allowed\');');
            } catch (e) {
                console.warn('eval not allowed');
                var chld = window.open(u.concat('&mode=window'), '_blank', 'left=8000,top=0,height=8000,width=360,location=no,toolbar=no,directories=no,status=no,menubar=no');
                if (!chld) alert('Error open window');
                return;
            }

            var x = new XMLHttpRequest();
            x.open('GET', u.concat('&mode=inline'), false);
            x.withCredentials = true;
            x.send(null);
            if(x.status == 200) {
                eval(x.responseText);
            } else {
                alert(x.statusText);
            }
        })();
        <?php /* END SCRIPT */ ?>

        <?php ob_start(); ?>
    </script>
<?php ob_end_clean(); ?>

<?php
$javascript = ob_get_clean();
$javascript = preg_replace('/(^\s*)|\r|\n/m', '', $javascript);
return 'javascript:' . $javascript;
