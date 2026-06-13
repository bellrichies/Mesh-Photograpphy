(function ($, window, document) {
    'use strict';

    function ensureToastRoot() {
        var existing = document.getElementById('admin-toast-root');
        if (existing) {
            return existing;
        }

        var root = document.createElement('div');
        root.id = 'admin-toast-root';
        root.className = 'pointer-events-none fixed right-4 top-4 z-[90] flex w-full max-w-sm flex-col gap-3';
        document.body.appendChild(root);
        return root;
    }

    function toastClasses(type) {
        if (type === 'success') {
            return 'border-emerald-200 bg-emerald-50 text-emerald-900';
        }

        if (type === 'warning') {
            return 'border-amber-200 bg-amber-50 text-amber-900';
        }

        return 'border-rose-200 bg-rose-50 text-rose-900';
    }

    function notify(message, type) {
        var text = String(message || '').trim();
        if (!text) {
            return;
        }

        var root = ensureToastRoot();
        var toast = document.createElement('div');
        toast.className = 'pointer-events-auto overflow-hidden rounded-2xl border px-4 py-3 shadow-soft transition duration-200 ' + toastClasses(type || 'error');
        toast.innerHTML = '<div class="flex items-start gap-3">'
            + '<div class="min-w-0 flex-1">'
            + '<p class="text-sm font-medium">' + $('<div>').text(text).html() + '</p>'
            + '</div>'
            + '<button type="button" class="rounded-full px-2 py-1 text-xs font-semibold text-slate-500" data-toast-close>Close</button>'
            + '</div>';

        root.appendChild(toast);

        var timeoutId = window.setTimeout(function () {
            $(toast).fadeOut(180, function () {
                toast.remove();
            });
        }, 3600);

        $(toast).on('click', '[data-toast-close]', function () {
            window.clearTimeout(timeoutId);
            $(toast).stop(true, true).fadeOut(180, function () {
                toast.remove();
            });
        });
    }

    function extractMessage(xhr, fallback) {
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
            return String(xhr.responseJSON.message);
        }

        return String(fallback || 'Request failed.');
    }

    function request(options) {
        var settings = $.extend(true, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }, options || {});

        return $.ajax(settings);
    }

    var AdminAjax = {
        request: request,
        get: function (url, data) {
            return request({
                url: url,
                method: 'GET',
                data: data || {}
            });
        },
        post: function (url, data) {
            return request({
                url: url,
                method: 'POST',
                data: $.extend({_ajax: '1'}, data || {})
            });
        },
        notify: notify,
        success: function (message) {
            notify(message, 'success');
        },
        warning: function (message) {
            notify(message, 'warning');
        },
        error: function (message) {
            notify(message, 'error');
        },
        extractMessage: extractMessage
    };

    window.AdminAjax = AdminAjax;
})(jQuery, window, document);