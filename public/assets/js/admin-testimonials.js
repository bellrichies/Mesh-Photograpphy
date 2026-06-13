(function ($) {
    'use strict';

    $(function () {
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

        if (!$('[data-tab-target]').length) {
            return;
        }

        $('[data-tab-target]').on('click', function () {
            activateTab(String($(this).data('tab-target') || 'content'));
        });

        activateTab('content');
    });
})(jQuery);