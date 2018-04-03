<?php
/**
 * @var array $params
 */
header('Content-type: text/javascript; charset=utf-8');
$overlay_url = $params['overlay_url'];
?>
<?php ob_start(); ?>
<script>
    <?php ob_end_clean(); ?>

    (function () {

        console.log("vvproject is booting");

        var vv;
        var subj;

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
            console.log("Document body not found. Overlay will be loaded in separate window");
            vv = window.open(<?= json_encode($overlay_url) ?>, '<?= OVERLAY_WINDOW_NAME ?>', '<?= OVERLAY_WINDOW_PARAMS ?>');
            if (!vv) location.href = href;
            return;
        }

        console.log("vvproject window creating");
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


        vvh = document.createElement('div');
        vvh.style.position = 'absolute';
        vvh.style.visibility = 'hidden';
        vvh.style.zIndex = 2147483647;
        vv.appendChild(vvh);
        vv.vvh = vvh;
        vvf = document.createElement('iframe');
        vvf.style.visibility = 'hidden';
        vvf.style.display = 'none';
        vvf.onload = function() {vvf.style.visibility = 'visible'; vvf.style.display = 'block'};
        vvf.className = 'vv';
        vvf.id = 'vvframe';
        vvf.name = 'vvframe';
        vvf.src = '';
        vvf.src = <?= json_encode($params['overlay_url']) ?>;
        vv.appendChild(vvf);
        console.log("vvproject iframe set up");
        vv.vvf = vvf;
        vvf.focus(); // TODO: Похоже, из-за этого в ИЕ боди при первом открытии подсвечивается

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

        var receiveMessage = function(event){
            if (event.data == 'close') {
                var vv = document.getElementById('vvwindow');
                if (vv) vv.parentNode.removeChild(vv);
            }
        };

        if (window.addEventListener){
            window.addEventListener("message",receiveMessage, false);
        } else {
            window.attachEvent("onmessage", receiveMessage);
        }

    })();


    <?php ob_start(); ?>
</script>
<?php ob_end_clean(); ?>
