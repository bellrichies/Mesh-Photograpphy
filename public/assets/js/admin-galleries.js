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

        function galleryConfig() {
            return window.GALLERY_ADMIN_CONFIG || null;
        }

        function categoryConfig() {
            return window.GALLERY_CATEGORY_CONFIG || null;
        }

        function generateGallerySlug(fromSlugField) {
            var config = galleryConfig();
            if (!config) {
                return;
            }

            var source = fromSlugField ? ($('#gallery-slug').val() || '') : ($('#gallery-title').val() || '');

            window.AdminAjax.get(config.adminPath + '/galleries/slug', {
                title: source,
                gallery_id: config.galleryId || ''
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    $('#gallery-slug-feedback').text('Unable to generate slug right now.');
                    return;
                }

                $('#gallery-slug').val(payload.data.slug || 'gallery');
                $('#gallery-slug-feedback').text('Slug ready: ' + (payload.data.slug || 'gallery'));
            }).fail(function () {
                $('#gallery-slug-feedback').text('Unable to generate slug right now.');
            });
        }

        function refreshGalleryMedia(html) {
            $('#gallery-media-manager').html(html || '');
        }

        function currentMediaOrder() {
            return $('[data-gallery-media-row]').map(function () {
                return Number($(this).data('media-id') || 0);
            }).get();
        }

        function postGalleryMedia(path, payload, onSuccess) {
            var config = galleryConfig();
            if (!config) {
                return;
            }

            payload = payload || {};
            payload[config.csrfKey] = config.csrfToken;

            window.AdminAjax.post(config.adminPath + path, payload).done(function (response) {
                if (!response || !response.ok) {
                    window.AdminAjax.error(response && response.message ? response.message : 'Gallery media request failed.');
                    return;
                }

                if (response.data && response.data.html) {
                    refreshGalleryMedia(response.data.html);
                }

                if (typeof onSuccess === 'function') {
                    onSuccess(response);
                }
                window.AdminAjax.success(response.message || 'Gallery updated.');
            }).fail(function (xhr) {
                window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Gallery media request failed.'));
            });
        }

        $('[data-tab-target]').on('click', function () {
            activateTab(String($(this).data('tab-target') || 'content'));
        });

        if (galleryConfig()) {
            $('#generate-gallery-slug').on('click', function () {
                generateGallerySlug(false);
            });

            $('#gallery-title').on('blur', function () {
                if (!$('#gallery-slug').val()) {
                    generateGallerySlug(false);
                }
            });

            $('#gallery-slug').on('blur', function () {
                if ($('#gallery-slug').val()) {
                    generateGallerySlug(true);
                }
            });

            var pickingGalleryMedia = false;

            $('#open-gallery-media-picker').on('click', function () {
                pickingGalleryMedia = true;
                $('#media-picker-modal').removeClass('hidden');
                $('#picker-search').trigger('click');
            });

            $(document).on('media-picker:selected', function (event, payload) {
                var config = galleryConfig();
                if (!config || !pickingGalleryMedia) {
                    return;
                }

                pickingGalleryMedia = false;
                postGalleryMedia('/galleries/media/attach', {
                    gallery_id: config.galleryId,
                    media_id: Number(payload && payload.id ? payload.id : 0),
                    caption: '',
                    is_featured: '0'
                });
            });

            $(document).on('click', '.gallery-media-save', function () {
                var row = $(this).closest('[data-gallery-media-row]');
                var config = galleryConfig();

                postGalleryMedia('/galleries/media/update', {
                    gallery_id: config.galleryId,
                    media_id: Number(row.data('media-id') || 0),
                    caption: row.find('.gallery-media-caption').val() || '',
                    is_featured: row.find('.gallery-media-featured').is(':checked') ? '1' : '0'
                });
            });

            $(document).on('click', '.gallery-media-remove', function () {
                var row = $(this).closest('[data-gallery-media-row]');
                var config = galleryConfig();

                if (!window.confirm('Remove this media item from the gallery?')) {
                    return;
                }

                postGalleryMedia('/galleries/media/remove', {
                    gallery_id: config.galleryId,
                    media_id: Number(row.data('media-id') || 0)
                });
            });

            $(document).on('click', '.gallery-media-move', function () {
                var row = $(this).closest('[data-gallery-media-row]');
                var direction = String($(this).data('direction') || 'up');
                var config = galleryConfig();

                if (direction === 'up') {
                    row.prev('[data-gallery-media-row]').before(row);
                } else {
                    row.next('[data-gallery-media-row]').after(row);
                }

                postGalleryMedia('/galleries/media/reorder', {
                    gallery_id: config.galleryId,
                    media_ids: currentMediaOrder()
                });
            });

            activateTab('content');
        }

        $(document).on('click', '.generate-category-slug', function () {
            var config = categoryConfig();
            if (!config) {
                return;
            }

            var form = $(this).closest('.category-form');
            var categoryId = String(form.data('category-id') || '');
            var name = form.find('.category-name').val() || form.find('.category-slug').val() || 'gallery-category';

            window.AdminAjax.get(config.adminPath + '/gallery-categories/slug', {
                name: name,
                category_id: categoryId
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    return;
                }

                form.find('.category-slug').val(payload.data.slug || 'gallery-category');
            });
        });
    });
})(jQuery);