<?php

use YY\System\YY;

// TODO: Maybe it's make sense to build dynamic script (depending on browser type or geo-location for example)

ob_start()
?>

<?php ob_start(); ?>
    <script>
        <?php ob_end_clean(); ?>

        <?php /* START SCRIPT */ ?>
        (function () {
            var u = 'https://<?= $_SERVER['HTTP_HOST'] ?>/?view=boot&version=<?= BOOT_VERSION ?>&guest=<?= YY::$ME['PUBLIC_KEY'] ?>&where='.concat(encodeURIComponent(location.toString()),'&title=',encodeURIComponent(document.title));

            var fallback = function() {
                u = u.concat('&mode=window','&nonce=',Math.random().toString());
                var child = window.open(u, '<?= OVERLAY_WINDOW_NAME ?>', '<?= OVERLAY_WINDOW_PARAMS ?>');
                if (child) {
                    addEventListener('message', function (e) {
                        if (e.origin == u) {
                            console.info(e.data);
                            eval(e.data);
                        } else {
                            console.warn(e);
                        }
                    });
                } else {
                    alert('Error open window. Should be enabled in your browser');
                }
            };

            try {
                eval('var a=1;');
                var x = new XMLHttpRequest();
                x.open('GET', u.concat('&mode=inline'), false);
                x.withCredentials = true;
                x.send(null);
            } catch (e) {
                console.warn(e);
                fallback();
                return;
            }
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
