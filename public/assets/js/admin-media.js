(function ($) {
    'use strict';

    function bytesToSize(bytes) {
        var n = Number(bytes || 0);
        if (n < 1024) return n + ' B';
        if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
        if (n < 1024 * 1024 * 1024) return (n / (1024 * 1024)).toFixed(1) + ' MB';
        return (n / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    $(function () {
        if (!window.MEDIA_LIBRARY_CONFIG) {
            return;
        }

        var config = window.MEDIA_LIBRARY_CONFIG;
        var page = 1;
        var totalPages = 1;
        var pickerPage = 1;
        var pickerTotalPages = 1;
        var hasMediaLibrary = $('#media-grid').length > 0;
        var activePickerTarget = null;
        var activePickerPreview = null;

        function apiUrl(path) {
            return config.adminPath + path;
        }

        function loadMedia(targetPage) {
            page = Math.max(1, targetPage || page || 1);

            window.AdminAjax.get(apiUrl('/media/search'), {
                q: $('#media-query').val() || '',
                type: $('#media-type').val() || '',
                sort: $('#media-sort').val() || 'newest',
                page: page,
                limit: 18
            }).done(function (payload) {
                if (!payload || !payload.ok) {
                    $('#media-grid').html('<p class="rounded border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">Unable to load media results.</p>');
                    return;
                }

                var items = payload.data.items || [];
                var pagination = payload.data.pagination || {};
                totalPages = Number(pagination.total_pages || 1);
                page = Number(pagination.page || 1);

                if (!items.length) {
                    $('#media-grid').html('<p class="col-span-full rounded border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-600">No media found for your filters.</p>');
                } else {
                    var cards = items.map(function (item) {
                        var usageLabel = (item.usage_count || 0) > 0
                            ? 'Used in ' + item.usage_count + ' place(s)'
                            : 'Not used yet';

                        var preview = item.file_type === 'image'
                            ? '<img src="' + escapeHtml(item.url) + '" alt="' + escapeHtml(item.title || item.original_name) + '" class="h-full w-full object-cover">'
                            : '<div class="flex h-full items-center justify-center text-xs uppercase tracking-[0.2em] text-slate-500">' + escapeHtml(item.file_type || 'file') + '</div>';

                        return '' +
                            '<article class="rounded-xl border border-slate-200 bg-white p-4" data-media-card data-media-id="' + Number(item.id) + '">' +
                                '<div class="mb-3 h-40 overflow-hidden rounded bg-slate-100">' + preview + '</div>' +
                                '<p class="truncate text-sm font-semibold text-slate-900">' + escapeHtml(item.title || item.original_name || 'Untitled') + '</p>' +
                                '<p class="mt-1 text-xs text-slate-500">' + escapeHtml(item.original_name || '') + '</p>' +
                                '<p class="mt-1 text-xs text-slate-500">' + escapeHtml(item.mime_type || '') + ' • ' + bytesToSize(item.size_bytes) + '</p>' +
                                '<p class="mt-1 text-xs text-indigo-600">' + escapeHtml(usageLabel) + '</p>' +
                                '<div class="mt-3 space-y-2">' +
                                    '<input type="text" class="media-title w-full rounded border border-slate-300 px-2 py-1 text-xs" placeholder="Title" value="' + escapeHtml(item.title || '') + '">' +
                                    '<input type="text" class="media-alt w-full rounded border border-slate-300 px-2 py-1 text-xs" placeholder="Alt text" value="' + escapeHtml(item.alt_text || '') + '">' +
                                    '<textarea class="media-caption w-full rounded border border-slate-300 px-2 py-1 text-xs" placeholder="Caption" rows="2">' + escapeHtml(item.caption || '') + '</textarea>' +
                                '</div>' +
                                '<div class="mt-3 flex gap-2">' +
                                    '<button type="button" class="media-save rounded bg-slate-900 px-3 py-1 text-xs text-white">Save</button>' +
                                    '<button type="button" class="media-delete rounded border border-rose-300 px-3 py-1 text-xs text-rose-700">Delete</button>' +
                                '</div>' +
                            '</article>';
                    });

                    $('#media-grid').html(cards.join(''));
                }

                $('#media-pagination-info').text('Page ' + page + ' of ' + totalPages + ' • Total ' + (pagination.total || 0));
                $('#media-prev').prop('disabled', page <= 1);
                $('#media-next').prop('disabled', page >= totalPages);
            }).fail(function () {
                $('#media-grid').html('<p class="rounded border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">Error loading media library.</p>');
            });
        }

        function uploadOne(file) {
            var formData = new FormData();
            formData.append(config.csrfKey, config.csrfToken);
            formData.append('file', file);

            var row = $('<div class="rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">Uploading ' + escapeHtml(file.name) + '...</div>');
            $('#upload-progress').prepend(row);

            window.AdminAjax.request({
                url: apiUrl('/media/upload'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
                }).done(function (payload) {
                if (payload && payload.ok) {
                    row.removeClass('bg-slate-50 border-slate-200').addClass('bg-emerald-50 border-emerald-200 text-emerald-700').text('Uploaded: ' + file.name);
                    window.AdminAjax.success(payload.message || ('Uploaded ' + file.name));
                } else {
                    row.removeClass('bg-slate-50 border-slate-200').addClass('bg-rose-50 border-rose-200 text-rose-700').text('Failed: ' + file.name + ' (' + (payload && payload.message ? payload.message : 'Unknown error') + ')');
                }
                loadMedia(page);
            }).fail(function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Upload failed';
                row.removeClass('bg-slate-50 border-slate-200').addClass('bg-rose-50 border-rose-200 text-rose-700').text('Failed: ' + file.name + ' (' + message + ')');
            });
        }

        if (hasMediaLibrary) {
            $('#media-search').on('click', function () {
                loadMedia(1);
            });

            $('#media-prev').on('click', function () {
                if (page > 1) {
                    loadMedia(page - 1);
                }
            });

            $('#media-next').on('click', function () {
                if (page < totalPages) {
                    loadMedia(page + 1);
                }
            });

            $('#bulk-upload-input').on('change', function () {
                var files = Array.prototype.slice.call(this.files || []);
                files.forEach(uploadOne);
                $(this).val('');
            });

            $('#media-grid').on('click', '.media-save', function () {
                var card = $(this).closest('[data-media-card]');
                var mediaId = Number(card.data('media-id'));

                window.AdminAjax.post(apiUrl('/media/update'), {
                    media_id: mediaId,
                    title: card.find('.media-title').val() || '',
                    alt_text: card.find('.media-alt').val() || '',
                    caption: card.find('.media-caption').val() || '',
                    description: '',
                    [config.csrfKey]: config.csrfToken
                }).done(function (payload) {
                    if (!payload || !payload.ok) {
                        window.AdminAjax.error(payload && payload.message ? payload.message : 'Save failed.');
                        return;
                    }
                    window.AdminAjax.success(payload.message || 'Media saved.');
                    loadMedia(page);
                }).fail(function (xhr) {
                    window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Error saving metadata.'));
                });
            });

            $('#media-grid').on('click', '.media-delete', function () {
                var card = $(this).closest('[data-media-card]');
                var mediaId = Number(card.data('media-id'));

                if (!window.confirm('Delete this media item?')) {
                    return;
                }

                window.AdminAjax.post(apiUrl('/media/delete'), {
                    media_id: mediaId,
                    [config.csrfKey]: config.csrfToken
                }).done(function (payload) {
                    if (!payload || !payload.ok) {
                        window.AdminAjax.error(payload && payload.message ? payload.message : 'Delete failed.');
                        return;
                    }
                    window.AdminAjax.success(payload.message || 'Media deleted.');
                    loadMedia(page);
                }).fail(function (xhr) {
                    window.AdminAjax.error(window.AdminAjax.extractMessage(xhr, 'Error deleting media.'));
                });
            });
        }

        function loadPicker(targetPage) {
            pickerPage = Math.max(1, targetPage || pickerPage || 1);

            window.AdminAjax.get(apiUrl('/media/picker'), {
                q: $('#picker-query').val() || '',
                type: $('#picker-type').val() || '',
                page: pickerPage,
                limit: 12
            }).done(function (payload) {
                if (!payload || !payload.ok || !payload.data) {
                    $('#picker-results').html('<p class="rounded border border-rose-200 bg-rose-50 px-4 py-6 text-center text-sm text-rose-700">Unable to load picker results.</p>');
                    $('#picker-pagination-info').text('Unable to load media.');
                    return;
                }

                var pagination = payload.data.pagination || {};
                pickerTotalPages = Number(pagination.total_pages || 1);
                pickerPage = Number(pagination.page || 1);
                $('#picker-results').html(payload.data.html || '');
                $('#picker-pagination-info').text('Page ' + pickerPage + ' of ' + pickerTotalPages + ' | Total ' + (pagination.total || payload.data.count || 0));
                $('#picker-prev').prop('disabled', pickerPage <= 1);
                $('#picker-next').prop('disabled', pickerPage >= pickerTotalPages);
            }).fail(function () {
                $('#picker-results').html('<p class="rounded border border-rose-200 bg-rose-50 px-4 py-6 text-center text-sm text-rose-700">Error loading picker.</p>');
                $('#picker-pagination-info').text('Error loading media.');
            });
        }

        $(document).on('click', '#open-picker', function () {
            activePickerTarget = null;
            activePickerPreview = null;
            pickerPage = 1;
            $('#media-picker-modal').removeClass('hidden');
            loadPicker();
        });

        $(document).on('click', '[data-open-picker]', function () {
            activePickerTarget = String($(this).data('target-input') || '');
            activePickerPreview = String($(this).data('target-preview') || '');
            pickerPage = 1;
            $('#media-picker-modal').removeClass('hidden');
            loadPicker();
        });

        $(document).on('click', '[data-picker-close]', function () {
            $('#media-picker-modal').addClass('hidden');
        });

        $(document).on('click', '#picker-search', function () {
            loadPicker(1);
        });

        $(document).on('keydown', '#picker-query', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadPicker(1);
            }
        });

        $(document).on('change', '#picker-type', function () {
            loadPicker(1);
        });

        $(document).on('click', '#picker-prev', function () {
            if (pickerPage > 1) {
                loadPicker(pickerPage - 1);
            }
        });

        $(document).on('click', '#picker-next', function () {
            if (pickerPage < pickerTotalPages) {
                loadPicker(pickerPage + 1);
            }
        });

        $(document).on('click', '[data-picker-select]', function () {
            var payload = {
                id: Number($(this).data('media-id') || 0),
                url: String($(this).data('media-url') || ''),
                name: String($(this).data('media-name') || '')
            };

            if (activePickerTarget) {
                $('#' + activePickerTarget).val(String(payload.id));
            }

            if (activePickerPreview) {
                $('#' + activePickerPreview).text(payload.name ? payload.name + ' (ID: ' + payload.id + ')' : 'Selected media ID: ' + payload.id);
            }

            $(document).trigger('media-picker:selected', [payload]);

            var mediaName = $(this).data('media-name');
            if (!activePickerTarget && !activePickerPreview) {
                window.AdminAjax.success('Selected media: ' + mediaName);
            }
            $('#media-picker-modal').addClass('hidden');
        });

        if (hasMediaLibrary && !config.pickerOnly) {
            loadMedia(1);
        }
    });
})(jQuery);
