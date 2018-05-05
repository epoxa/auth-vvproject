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
            var u = 'https://<?= $_SERVER['HTTP_HOST'] ?>/?view=boot&version=<?= BOOT_VERSION ?>&guest=<?= YY::$ME['PUBLIC_KEY'] ?>&where='.concat(encodeURIComponent(location.href),'&title=',encodeURIComponent(document.title),'&nonce=',Math.random().toString());

            var fallback = function() {
                var w = u.concat('&mode=window');
                var child = window.open(w, '<?= OVERLAY_WINDOW_NAME ?>-'.concat(location.href), '<?= OVERLAY_WINDOW_PARAMS ?>');
                if (child) {
                    addEventListener('message', function (e) {
                        if (e.origin == w) {
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
                x.send();
                if(x.status == 200) {
                    eval(x.responseText);
                } else {
                    throw new XMLHttpRequestException();
                }
            } catch (e) {
                console.warn(e);
                fallback();
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
