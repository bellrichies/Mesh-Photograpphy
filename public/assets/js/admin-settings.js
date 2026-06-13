(function ($) {
    'use strict';

    $(function () {
        if (!window.MEDIA_LIBRARY_CONFIG) {
            return;
        }

        var activeInputId = null;
        var activePreviewId = null;

        $(document).on('click', '[data-open-picker]', function () {
            activeInputId = String($(this).data('target-input') || '');
            activePreviewId = String($(this).data('target-preview') || '');
        });

        $(document).on('media-picker:selected', function (_event, payload) {
            if (!payload || !activeInputId) {
                return;
            }

            var idValue = String(payload.id || '');
            $('#' + activeInputId).val(idValue);

            if (activePreviewId) {
                var label = payload.name ? String(payload.name) : idValue;
                $('#' + activePreviewId).text('Selected media: ' + label + ' (ID: ' + idValue + ')');
            }
        });
    });
})(jQuery);
