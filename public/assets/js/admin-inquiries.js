(function ($) {
    'use strict';

    $(function () {
        if (!window.INQUIRY_ADMIN_CONFIG || !window.AdminAjax) {
            return;
        }

        var config = window.INQUIRY_ADMIN_CONFIG;
        var results = $('#inquiry-results');
        var form = $('#inquiry-filter-form');
        var requestHandle = null;

        function endpoint(path) {
            return config.adminPath + path;
        }

        function formDataWithPage(page) {
            var data = form.serializeArray();
            var hasPage = false;

            data = data.map(function (entry) {
                if (entry.name === 'page') {
                    hasPage = true;
                    return {name: 'page', value: String(page || 1)};
                }

                return entry;
            });

            if (!hasPage) {
                data.push({name: 'page', value: String(page || 1)});
            }

            return data;
        }

        function loadResults(page) {
            if (requestHandle && typeof requestHandle.abort === 'function') {
                requestHandle.abort();
            }

            results.addClass('opacity-60');

            requestHandle = window.AdminAjax.get(endpoint('/inquiries/filter'), formDataWithPage(page)).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    window.AdminAjax.error(payload && payload.message ? payload.message : 'Unable to load inquiries.');
                    return;
                }

                results.html(payload.data.html || '');
            }).fail(function (xhr, status) {
                if (status !== 'abort') {
                    window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Unable to load inquiries.'));
                }
            }).always(function () {
                results.removeClass('opacity-60');
                requestHandle = null;
            });
        }

        form.on('submit', function (event) {
            event.preventDefault();
            loadResults(1);
        });

        $(document).on('click', '[data-inquiry-page]', function (event) {
            event.preventDefault();

            var page = Number($(this).data('inquiry-page') || 1);
            loadResults(page);
        });

        $(document).on('change', '[data-inquiry-status-select]', function () {
            var select = $(this);
            var row = select.closest('[data-inquiry-row]');
            var inquiryId = Number(row.data('inquiry-id') || 0);
            var nextStatus = String(select.val() || 'new');
            var previousStatus = String(select.data('previous-status') || 'new');

            select.prop('disabled', true);

            window.AdminAjax.post(endpoint('/inquiries/status/' + inquiryId), {
                status: nextStatus,
                [config.csrfKey]: config.csrfToken
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    select.val(previousStatus);
                    window.AdminAjax.error(payload && payload.message ? payload.message : 'Unable to update inquiry status.');
                    return;
                }

                select.data('previous-status', payload.data.status || nextStatus);
                row.find('[data-inquiry-status-badge]').attr('class', payload.data.badge_class || '').text(payload.data.status_label || nextStatus);
                window.AdminAjax.success(payload.message || 'Inquiry status updated.');
            }).fail(function (xhr) {
                select.val(previousStatus);
                window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Unable to update inquiry status.'));
            }).always(function () {
                select.prop('disabled', false);
            });
        });
    });
})(jQuery);