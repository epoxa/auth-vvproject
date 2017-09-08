function initLanguageEditor(htmlId, $data) {
    var currentSlug;
    $robot = $('#_YY_' + htmlId);
    $robot.find('#list').DataTable({
        destroy: true,
        data: $data,
        columns: [
            { title: 'Slug', className: 'slug' },
            { title: 'Original' },
            { title: 'Translation', className: 'translation' }
        ],
        scrollCollapse: true,
        paging:         false,
        info: false
    });
    $robot.find('td.translation').editable({
        toggleFontSize : false,
        closeOnEnter : true,
        event: 'mousedown',
        callback: function(data) {
            var slug = data.$el.closest('tr').find('.slug').text();
            var value = data.content;
            if (value !== false) {
                $.ajax(
                    '?who=' + viewId + '-' + htmlId +'&get=ajaxSetTranslation&s_slug=' + slug + '&s_value=' + encodeURIComponent(value),
                    {

                    });
            }
        }
    });
    $robot.on('keypress','textarea', function(e) {
        var target = e.target;
        var pos = getCaretPosition(target);
        var $r;
        switch (e.keyCode) {
            case 27:
                currentSlug = $(this).closest('tr').find('td.slug').text();
                $robot.find('#list_filter input').focus();
                return false;
            case 38:
                if (pos > 0) return;
                $r = $(this).closest('tr').prev('tr').find('td.translation');
                if (!$r.length) return;
                $r.editable('open');
                e.preventDefault();
                return false;
            case 13:
            case 40:
                if (pos < $(target).val().length) return;
                $r = $(this).closest('tr').next('tr').find('td.translation');
                if (!$r.length) return;
                $r.editable('open');
                e.preventDefault();
                return false;
        }
    }).on('keyup', 'textarea', function(e) {
        ensureEnoughHeight($(this).get(0));
    }).on('keypress', '#list_filter input', function(e) {
        switch (e.keyCode) {
            case 13:
            case 27:
            case 40:
                $robot.find('td.translation').first().editable('open');
                return false;
        }
    });

}

function getCaretPosition(obj) {
    var cursorPos = null;
    if (document.selection) {
        var range = document.selection.createRange();
        range.moveStart('textedit', -1);
        cursorPos = range.text.length;
    } else {
        cursorPos = obj.selectionStart;
    }
    return cursorPos;
}

function findPreviousFocusable (elem, skip) {
    if (!elem) return null;
    var oldTop = $(elem).offset().top;
    var newFocus = elem;
    while (true) {
        if (newFocus.previousSibling) {
            newFocus = newFocus.previousSibling;
            while (newFocus.lastChild) newFocus = newFocus.lastChild;
        } else {
            newFocus = newFocus.parentNode;
        }
        if (!newFocus) break;
        if (newFocus.nodeType == 3) continue;
        if (newFocus == document.body) break;
        if (newFocus.tabIndex < 0) continue;
        var nf = $(newFocus);
        if (nf.is(':hidden') || nf.css('visibility') == 'hidden') continue;
        if (!skip) break;
        if (nf.hasClass('yy-skip')) continue;
        if (nf.offset().top >= oldTop) continue;
        break;
    }
    if (newFocus.focus && newFocus != document.body) return newFocus;
    else return null;
}

function findNextFocusable (elem, skip) {
    if (!elem) return null;
    var oldTop = $(elem).offset().top;
    var newFocus = elem;
    while (true) {
        if (newFocus.firstChild)
            newFocus = newFocus.firstChild;
        else if (newFocus.nextSibling)
            newFocus = newFocus.nextSibling;
        else {
            while (newFocus.parentNode && !newFocus.nextSibling && newFocus != document.body) newFocus = newFocus.parentNode;
            if (newFocus.nextSibling) newFocus = newFocus.nextSibling;
            else newFocus = null;
        }
        if (!newFocus) break;
        if (newFocus.nodeType == 3) continue;
        if (newFocus.tabIndex < 0) continue;
        var nf = $(newFocus);
        if (nf.is(':hidden') || nf.css('visibility') == 'hidden') continue;
        if (!skip) break;
        if (nf.hasClass('yy-skip')) continue;
        if (nf.offset().top <= oldTop) continue;
        break;
    }
    if (newFocus && newFocus.focus) return newFocus;
    else return null;
}

function setNewFocus(oldFocus, newFocus, forward) {
    if (!newFocus) return;
    newFocus.focus();
}

function prepareTextarea(textarea) {
    if (!textarea) return;
    ensureEnoughHeight(textarea);
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function ensureEnoughHeight(textarea) {
    if ((delta = textarea.scrollHeight - textarea.offsetHeight) > 0) {
        textarea.style.height = '' + (textarea.offsetHeight + delta) + 'px';
    }
}
