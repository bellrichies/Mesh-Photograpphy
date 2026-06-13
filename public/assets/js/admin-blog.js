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

        function generatePostSlug(fromSlugField) {
            if (!window.BLOG_POST_ADMIN_CONFIG) {
                return;
            }

            var source = fromSlugField ? ($('#blog-post-slug').val() || '') : ($('#blog-post-title').val() || '');

            window.AdminAjax.get(window.BLOG_POST_ADMIN_CONFIG.adminPath + '/blog/posts/slug', {
                title: source,
                post_id: window.BLOG_POST_ADMIN_CONFIG.postId || ''
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    $('#blog-post-slug-feedback').text('Unable to generate slug right now.');
                    return;
                }

                $('#blog-post-slug').val(payload.data.slug || 'blog-post');
                $('#blog-post-slug-feedback').text('Slug ready: ' + (payload.data.slug || 'blog-post'));
            }).fail(function () {
                $('#blog-post-slug-feedback').text('Unable to generate slug right now.');
            });
        }

        function refreshPostMedia(html) {
            $('#blog-post-media-manager').html(html || '');
        }

        function currentPostMediaOrder() {
            return $('[data-blog-post-media-row]').map(function () {
                return Number($(this).data('media-id') || 0);
            }).get();
        }

        function postMediaRequest(path, payload, onSuccess) {
            if (!window.BLOG_POST_ADMIN_CONFIG) {
                return;
            }

            payload = payload || {};
            payload[window.BLOG_POST_ADMIN_CONFIG.csrfKey] = window.BLOG_POST_ADMIN_CONFIG.csrfToken;

            window.AdminAjax.post(window.BLOG_POST_ADMIN_CONFIG.adminPath + path, payload).done(function (response) {
                if (!response || !response.ok) {
                    window.AdminAjax.error(response && response.message ? response.message : 'Blog media request failed.');
                    return;
                }

                if (response.data && response.data.html) {
                    refreshPostMedia(response.data.html);
                }

                if (typeof onSuccess === 'function') {
                    onSuccess(response);
                }
                window.AdminAjax.success(response.message || 'Blog media updated.');
            }).fail(function (xhr) {
                window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Blog media request failed.'));
            });
        }

        function autosaveStatus(message) {
            $('#blog-post-autosave-status').text(message || 'Waiting for edits.');
        }

        function startAutosave() {
            if (!window.BLOG_POST_ADMIN_CONFIG || !window.BLOG_POST_ADMIN_CONFIG.autosaveUrl) {
                return;
            }

            var form = $('#blog-post-editor-form');
            var timer = null;
            var lastSnapshot = '';

            function payload() {
                return {
                    title: $('#blog-post-title').val() || '',
                    slug: $('#blog-post-slug').val() || '',
                    excerpt: form.find('[name="excerpt"]').val() || '',
                    body_long: form.find('[name="body_long"]').val() || '',
                    meta_summary: form.find('[name="meta_summary"]').val() || '',
                    [window.BLOG_POST_ADMIN_CONFIG.csrfKey]: window.BLOG_POST_ADMIN_CONFIG.csrfToken
                };
            }

            function snapshot(data) {
                return JSON.stringify(data);
            }

            function queueAutosave() {
                var data = payload();
                var currentSnapshot = snapshot(data);

                if (currentSnapshot === lastSnapshot) {
                    return;
                }

                autosaveStatus('Saving draft...');
                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    window.AdminAjax.post(window.BLOG_POST_ADMIN_CONFIG.autosaveUrl, data).done(function (response) {
                        if (!response || !response.ok || !response.data) {
                            autosaveStatus(response && response.message ? response.message : 'Autosave failed.');
                            return;
                        }

                        lastSnapshot = currentSnapshot;

                        if (response.data.slug) {
                            $('#blog-post-slug').val(response.data.slug);
                        }

                        if (response.data.reading_time) {
                            $('[data-autosave-reading-time]').text(response.data.reading_time + ' min');
                        } else {
                            $('[data-autosave-reading-time]').text('Auto');
                        }

                        autosaveStatus('Last autosave: ' + (response.data.saved_at || 'just now'));
                    }).fail(function (xhr) {
                        autosaveStatus(window.AdminAjax.extractMessage(xhr, 'Autosave failed.'));
                    });
                }, 1200);
            }

            form.on('input', '#blog-post-title, #blog-post-slug, [name="excerpt"], [name="body_long"], [name="meta_summary"]', queueAutosave);
            form.on('submit', function () {
                window.clearTimeout(timer);
            });

            lastSnapshot = snapshot(payload());
        }

        $('[data-tab-target]').on('click', function () {
            activateTab(String($(this).data('tab-target') || 'content'));
        });

        if (window.BLOG_POST_ADMIN_CONFIG) {
            $('#generate-blog-post-slug').on('click', function () {
                generatePostSlug(false);
            });

            $('#blog-post-title').on('blur', function () {
                if (!$('#blog-post-slug').val()) {
                    generatePostSlug(false);
                }
            });

            $('#blog-post-slug').on('blur', function () {
                if ($('#blog-post-slug').val()) {
                    generatePostSlug(true);
                }
            });

            var pickingPostMedia = false;

            $('#open-blog-post-media-picker').on('click', function () {
                pickingPostMedia = true;
                $('#media-picker-modal').removeClass('hidden');
                $('#picker-search').trigger('click');
            });

            $(document).on('media-picker:selected', function (event, payload) {
                if (!window.BLOG_POST_ADMIN_CONFIG || !pickingPostMedia) {
                    return;
                }

                pickingPostMedia = false;
                postMediaRequest('/blog/posts/media/attach', {
                    post_id: window.BLOG_POST_ADMIN_CONFIG.postId,
                    media_id: Number(payload && payload.id ? payload.id : 0),
                    caption: ''
                });
            });

            $(document).on('click', '.blog-post-media-save', function () {
                var row = $(this).closest('[data-blog-post-media-row]');
                postMediaRequest('/blog/posts/media/update', {
                    post_id: window.BLOG_POST_ADMIN_CONFIG.postId,
                    media_id: Number(row.data('media-id') || 0),
                    caption: row.find('.blog-post-media-caption').val() || ''
                });
            });

            $(document).on('click', '.blog-post-media-remove', function () {
                var row = $(this).closest('[data-blog-post-media-row]');
                if (!window.confirm('Remove this media item from the blog post?')) {
                    return;
                }

                postMediaRequest('/blog/posts/media/remove', {
                    post_id: window.BLOG_POST_ADMIN_CONFIG.postId,
                    media_id: Number(row.data('media-id') || 0)
                });
            });

            $(document).on('click', '.blog-post-media-move', function () {
                var row = $(this).closest('[data-blog-post-media-row]');
                var direction = String($(this).data('direction') || 'up');

                if (direction === 'up') {
                    row.prev('[data-blog-post-media-row]').before(row);
                } else {
                    row.next('[data-blog-post-media-row]').after(row);
                }

                postMediaRequest('/blog/posts/media/reorder', {
                    post_id: window.BLOG_POST_ADMIN_CONFIG.postId,
                    media_ids: currentPostMediaOrder()
                });
            });

            startAutosave();
            activateTab('content');
        }

        $(document).on('click', '.generate-blog-category-slug', function () {
            if (!window.BLOG_CATEGORY_CONFIG) {
                return;
            }

            var form = $(this).closest('.blog-category-form');
            var categoryId = String(form.data('category-id') || '');
            var name = form.find('.blog-category-name').val() || form.find('.blog-category-slug').val() || 'blog-category';

            window.AdminAjax.get(window.BLOG_CATEGORY_CONFIG.adminPath + '/blog/categories/slug', {
                name: name,
                category_id: categoryId
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    return;
                }

                form.find('.blog-category-slug').val(payload.data.slug || 'blog-category');
            });
        });

        $(document).on('click', '.generate-blog-tag-slug', function () {
            if (!window.BLOG_TAG_CONFIG) {
                return;
            }

            var form = $(this).closest('.blog-tag-form');
            var tagId = String(form.data('tag-id') || '');
            var name = form.find('.blog-tag-name').val() || form.find('.blog-tag-slug').val() || 'blog-tag';

            window.AdminAjax.get(window.BLOG_TAG_CONFIG.adminPath + '/blog/tags/slug', {
                name: name,
                tag_id: tagId
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    return;
                }

                form.find('.blog-tag-slug').val(payload.data.slug || 'blog-tag');
            });
        });
    });
})(jQuery);