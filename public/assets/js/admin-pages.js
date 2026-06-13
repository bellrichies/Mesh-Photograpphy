(function ($) {
    'use strict';

    $(function () {
        if (!window.PAGE_EDITOR_CONFIG) {
            return;
        }

        function activateTab(name) {
            $('[data-tab-target]').each(function () {
                var isActive = $(this).data('tab-target') === name;
                $(this)
                    .toggleClass('border-slate-900 bg-slate-900 text-white', isActive)
                    .toggleClass('border-slate-300 bg-white text-slate-700', !isActive);
            });

            $('[data-tab-panel]').each(function () {
                $(this).toggleClass('hidden', $(this).data('tab-panel') !== name);
            });
        }

        function generateSlug(fromSlugField) {
            var source = fromSlugField ? ($('#page-slug').val() || '') : ($('#page-title').val() || '');

            window.AdminAjax.get(window.PAGE_EDITOR_CONFIG.adminPath + '/pages/slug', {
                title: source,
                page_id: window.PAGE_EDITOR_CONFIG.pageId || ''
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    $('#slug-feedback').text('Unable to generate slug right now.');
                    return;
                }

                $('#page-slug').val(payload.data.slug || 'page');
                $('#slug-feedback').text('Slug ready: ' + (payload.data.slug || 'page'));
            }).fail(function () {
                $('#slug-feedback').text('Unable to generate slug right now.');
            });
        }

        $('[data-tab-target]').on('click', function () {
            activateTab(String($(this).data('tab-target') || 'content'));
        });

        $('#generate-slug').on('click', function () {
            generateSlug(false);
        });

        $('#page-title').on('blur', function () {
            if (!$('#page-slug').val()) {
                generateSlug(false);
            }
        });

        $('#page-slug').on('blur', function () {
            if ($('#page-slug').val()) {
                generateSlug(true);
            }
        });

        activateTab('content');
    });
})(jQuery);