/**
 * Created by epoxa on 12.02.17.
 */

(function () {

    window.yy_translate_agent = this;

    var yyid;
    var originalText;

    this.setTranslatorHandle = function (handle) {
        yyid = handle;
    };

    this.close = function () {
    };

    this.registerTranslatable = function (slug) {
        var $el = $('*[data-translate-slug="' + slug + '"]');
        if ($el.length) {
            $el.on('contextmenu', function () {
                return false;
            }).editable({
                toggleFontSize: false,
                closeOnEnter: true,
                callback: function (data) {
                    var value = data.content;
                    if (value !== false) {
                        go(yyid, 'setTranslation', {
                            s_slug: slug,
                            s_translation: value
                        });
                        //$.ajax(
                        //    '?who=' + viewId + '-' + yyid + '&get=ajaxSetTranslation&s_slug=' + slug + '&s_translation=' + encodeURIComponent(value),
                        //    {});
                    } else {
                        console.warn('content = false');
                    }
                }
            }).on('mousedown', function (e) {
                if (e.originalEvent.which == 3) {
                    setTimeout(function () {
                        originalText = $el.html();
                        console.info(originalText);
                        $el.editable('open');
                        $el.on('keypress', 'textarea', function (e) {
                            if (e.originalEvent.keyCode == 27) {
                                $el.editable('close');
                            }
                        });
                    }, 20);
                    //e.stopPropagation();
                    //e.preventDefault();
                    //return false;
                }
            });
        } else {
            console.warn('Slug not found: ' + slug);
        }

    };

})();

