<?php
/**
 * @var array $params
 */
use YY\System\YY;

header('Content-type: text/javascript; charset=utf-8');
$overlay_url = $params['overlay_url'];
$redirect_token = $params['redirect_token'];
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    (function () {

        console.log("vvproject is booting");

        var vv;

        window.VVProject = window.VVProject || {};
        var $ = window.VVProject.auth = window.VVProject.auth || {};

        function removeFrameGuards() {
            clearTimeout($.childGuard);
            removeEventListener('securitypolicyviolation', $.cspListener);
        }

        function giveUpLoadingFrame() {
            removeFrameGuards();
            closeChildFrame();
            fallback();
        }

        function closeChildFrame() {
            removeEventListener('message', $.frameListener);
            var vv = document.getElementById('vvwindow');
            if (vv) {
                console.trace('close vvproject frame');
                vv.parentNode.removeChild(vv);
            }
        }

        function fallback() {
            var vv = window.open(<?= json_encode($overlay_url) ?>, '<?= OVERLAY_WINDOW_NAME ?>-'.concat(location.href), '<?= OVERLAY_WINDOW_PARAMS ?>');
            if (vv) {
                console.log("Overlay will be loaded in separate window");
            } else {
                console.log("Overlay will be loaded in this window");
                location.href = <?= json_encode($overlay_url) ?>;
            }
        }

        // Удаляем загрузчик
        while (vv = document.getElementById('vv')) {
            vv.parentNode.removeChild(vv);
        }

        // Если окно открыто, то тупо его закрываем
        if (vv = document.getElementById('vvwindow')) {
            vv.parentNode.removeChild(vv);
            console.log("vvproject window closed");
            return;
        }

        if (!document.getElementsByTagName('body').length) {
            fallback();
            return;
        }

        console.log("vvproject frame creating");
        vv = document.createElement('div');
        document.getElementsByTagName('body')[0].appendChild(vv);
        vv.id = 'vvwindow';
        vv.style.position = 'fixed';
        vv.style.top = '0';
        vv.style.right = '0';
        vv.style.height = '100%';
        vv.style.minWidth = '280px';
        vv.style.maxWidth = '400px';
        vv.style.width = '20%';
        vv.style.zIndex = 2147483647;

        var vvh = document.createElement('div');
        vvh.style.position = 'absolute';
        vvh.style.visibility = 'hidden';
        vvh.style.zIndex = 2147483647;
        vv.appendChild(vvh);
        vv.vvh = vvh;
        var vvf = document.createElement('iframe');
        vvf.style.visibility = 'hidden';
        vvf.style.display = 'none';
        vvf.onload = function() {
            vvf.style.visibility = 'visible';
            vvf.style.display = 'block';
            vvf.focus();
        };
        vvf.className = 'vv';
        vvf.id = 'vvframe';
        vvf.name = 'vvframe';

        if (!$.cspListener) {
            $.cspListener = function(e) {
//                if (e.violatedDirective == 'frame-src' || e.violatedDirective == 'child-src')
                console.warn(e);
                giveUpLoadingFrame();
            };
        }

        addEventListener('securitypolicyviolation', $.cspListener);


        vvf.src = 'https://<?= $_SERVER['HTTP_HOST'] ?>/?view=loader&token=<?= $redirect_token ?>';

        if ($.frameListener) {
            // TODO: Create one universal listener for all cases
            console.trace('Remove old child frame listener');
            removeEventListener('message', $.frameListener)
        }
        $.frameListener = function (e) {
            if (e.source == vvf.contentWindow) {
                if (e.data == 'close') {
                    closeChildFrame();
                } else if (e.data == 'loading') {
                    console.log('frame loading started successfully');
                    removeFrameGuards();
                }
            }
        };
        addEventListener('message', $.frameListener);

        console.log("vvproject iframe set up");
        vv.appendChild(vvf);
        vv.vvf = vvf;

        $.childGuard = setTimeout(function() {
            console.log('Frame still not loaded. Trying fallback window');
            giveUpLoadingFrame();
            removeEventListener('securitypolicyviolation', $.cspListener);
            closeChildFrame();
            fallback();
        }, 1500);

        var vvt = document.getElementById('vvtheme');
        var src = location.protocol + '//<?= ROOT_URL ?>/css/marginalia.css';
        if (!vvt || vvt.href != src) {
            if (vvt) vvt.parentNode.removeChild(vvt);
            vvt = document.createElement('link');
            vvt.id = 'vvtheme';
            vvt.rel = 'stylesheet';
            vvt.type = 'text/css';
            vvt.href = '';
            vvt.href = src;
            document.getElementsByTagName('head')[0].appendChild(vvt);
            console.log("vvproject styles inserted");
        }

    })();


    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
