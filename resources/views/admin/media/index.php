<?php $tokenKey = (string) config('app.csrf_token_name', '_token'); ?>
<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Media Library</h2>
                <p class="mt-1 text-sm text-slate-600">Upload, search, filter, preview, update metadata, and manage reusable media assets.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" id="open-picker" class="rounded border border-slate-300 px-4 py-2 text-sm">Open Picker</button>
                <label class="cursor-pointer rounded bg-slate-900 px-4 py-2 text-sm text-white">
                    Bulk Upload
                    <input id="bulk-upload-input" type="file" class="hidden" multiple>
                </label>
            </div>
        </div>

        <div class="mb-4 grid gap-3 md:grid-cols-4">
            <input id="media-query" type="text" class="rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Search by title, name, alt text">
            <select id="media-type" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">All Types</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
                <option value="document">Documents</option>
            </select>
            <select id="media-sort" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="name">Name</option>
                <option value="size">Size</option>
            </select>
            <button id="media-search" type="button" class="rounded bg-slate-900 px-4 py-2 text-sm text-white">Apply</button>
        </div>

        <div id="upload-progress" class="mb-4 space-y-2"></div>

        <div id="media-grid" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>

        <div class="mt-5 flex items-center justify-between">
            <p id="media-pagination-info" class="text-sm text-slate-600">Loading...</p>
            <div class="flex gap-2">
                <button id="media-prev" type="button" class="rounded border border-slate-300 px-3 py-1 text-sm">Prev</button>
                <button id="media-next" type="button" class="rounded border border-slate-300 px-3 py-1 text-sm">Next</button>
            </div>
        </div>
    </div>
</section>

<?php include dirname(__DIR__, 1) . '/../components/media-picker.php'; ?>

<script>
    window.MEDIA_LIBRARY_CONFIG = {
        adminPath: <?= json_encode((string) ($adminPath ?? '/admin')) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
