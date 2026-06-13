(function ($) {
    'use strict';

    $(function () {
        if (!window.SERVICE_EDITOR_CONFIG) {
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
            var source = fromSlugField ? ($('#service-slug').val() || '') : ($('#service-title').val() || '');

            $.get(window.SERVICE_EDITOR_CONFIG.adminPath + '/services/slug', {
                title: source,
                service_id: window.SERVICE_EDITOR_CONFIG.serviceId || ''
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    $('#service-slug-feedback').text('Unable to generate slug right now.');
                    return;
                }

                $('#service-slug').val(payload.data.slug || 'service');
                $('#service-slug-feedback').text('Slug ready: ' + (payload.data.slug || 'service'));
            }).fail(function () {
                $('#service-slug-feedback').text('Unable to generate slug right now.');
            });
        }

        $('[data-tab-target]').on('click', function () {
            activateTab(String($(this).data('tab-target') || 'content'));
        });

        $('#generate-service-slug').on('click', function () {
            generateSlug(false);
        });

        $('#service-title').on('blur', function () {
            if (!$('#service-slug').val()) {
                generateSlug(false);
            }
        });

        $('#service-slug').on('blur', function () {
            if ($('#service-slug').val()) {
                generateSlug(true);
            }
        });

        activateTab('content');
    });
})(jQuery);