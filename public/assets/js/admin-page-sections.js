(function ($) {
    'use strict';

    $(function () {
        if (!window.PAGE_SECTION_CONFIG) {
            return;
        }

        var config = window.PAGE_SECTION_CONFIG;

        function endpoint(path) {
            return config.adminPath + '/pages/' + config.pageId + '/sections/' + path;
        }

        function updateSortLabels() {
            $('#section-list').find('[data-section-card]').each(function (index) {
                $(this).find('[data-sort-order]').text(index + 1);
            });
        }

        function badgeClass(status) {
            if (status === 'published') {
                return 'inline-flex rounded-full px-2 py-1 text-[11px] font-medium bg-emerald-100 text-emerald-700';
            }

            if (status === 'hidden') {
                return 'inline-flex rounded-full px-2 py-1 text-[11px] font-medium bg-slate-300 text-slate-700';
            }

            return 'inline-flex rounded-full px-2 py-1 text-[11px] font-medium bg-amber-100 text-amber-700';
        }

        function nextStatus(status) {
            if (status === 'published') {
                return 'hidden';
            }

            if (status === 'hidden') {
                return 'draft';
            }

            return 'published';
        }

        $(document).on('click', '[data-section-reorder]', function () {
            var card = $(this).closest('[data-section-card]');
            var sectionId = Number(card.data('section-id') || 0);
            var direction = String($(this).data('direction') || '');

            window.AdminAjax.post(endpoint('reorder/' + sectionId), {
                direction: direction,
                [config.csrfKey]: config.csrfToken
            }).done(function (payload) {
                if (!payload || !payload.ok) {
                    window.AdminAjax.error(payload && payload.message ? payload.message : 'Unable to reorder section.');
                    return;
                }

                if (direction === 'up') {
                    card.prev('[data-section-card]').before(card);
                } else {
                    card.next('[data-section-card]').after(card);
                }

                updateSortLabels();
                window.AdminAjax.success(payload.message || 'Section order updated.');
            }).fail(function (xhr) {
                window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Unable to reorder section.'));
            });
        });

        $(document).on('click', '[data-section-status]', function () {
            var card = $(this).closest('[data-section-card]');
            var button = $(this);
            var sectionId = Number(card.data('section-id') || 0);
            var status = String($(this).data('status') || 'draft');

            window.AdminAjax.post(endpoint('status/' + sectionId), {
                status: status,
                [config.csrfKey]: config.csrfToken
            }).done(function (payload) {
                if (!payload || !payload.ok) {
                    window.AdminAjax.error(payload && payload.message ? payload.message : 'Unable to update status.');
                    return;
                }

                var currentStatus = String((payload.data && payload.data.status) || status);
                var next = nextStatus(currentStatus);
                card.find('[data-status-label]').attr('class', badgeClass(currentStatus)).text(currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1));
                button.data('status', next).text('Set ' + next.charAt(0).toUpperCase() + next.slice(1));
                window.AdminAjax.success(payload.message || 'Section status updated.');
            }).fail(function (xhr) {
                window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Unable to update status.'));
            });
        });
    });
})(jQuery);