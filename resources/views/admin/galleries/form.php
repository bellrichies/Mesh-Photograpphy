<?php
$gallery = isset($gallery) && is_array($gallery) ? $gallery : null;
$values = isset($values) && is_array($values) ? $values : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$galleryId = $gallery !== null ? (int) ($gallery['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/galleries/store' : $adminPath . '/galleries/update/' . $galleryId;
$coverMediaId = is_scalar($values['cover_media_id'] ?? '') ? (string) ($values['cover_media_id'] ?? '') : '';
$selectedCategoryIds = isset($values['category_ids']) && is_array($values['category_ids']) ? array_map('strval', $values['category_ids']) : [];
$tokenKey = (string) ($tokenKey ?? '_token');
$galleryMediaHtml = (string) ($galleryMediaHtml ?? '');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Gallery' : 'Edit Gallery' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Build premium portfolio stories with controlled metadata, category placement, cover media, and ordered gallery imagery.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= htmlspecialchars($adminPath . '/gallery-categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Manage Categories</a>
                <a href="<?= htmlspecialchars($adminPath . '/galleries', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Galleries</a>
            </div>
        </div>

        <div class="mb-5 flex flex-wrap gap-2" data-tabs-nav>
            <button type="button" class="rounded-full border border-slate-900 bg-slate-900 px-4 py-2 text-sm text-white" data-tab-target="content">Content</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="categories">Categories</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="media">Media</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="publish">Publish Settings</button>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div data-tab-panel="content" class="space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                        <input id="gallery-title" type="text" name="title" value="<?= htmlspecialchars((string) ($values['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
                        <div class="flex gap-2">
                            <input id="gallery-slug" type="text" name="slug" value="<?= htmlspecialchars((string) ($values['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <button type="button" id="generate-gallery-slug" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">Generate</button>
                        </div>
                        <p id="gallery-slug-feedback" class="mt-1 text-xs text-slate-500">Slug will be normalized and kept unique.</p>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Excerpt</span>
                        <textarea name="excerpt" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($values['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Story Intro</span>
                        <textarea name="story_intro" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Editorial intro shown on the gallery detail page."><?= htmlspecialchars((string) ($values['story_intro'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Client Name</span>
                        <input type="text" name="client_name" value="<?= htmlspecialchars((string) ($values['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Location</span>
                        <input type="text" name="location" value="<?= htmlspecialchars((string) ($values['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>
            </div>

            <div data-tab-panel="categories" class="hidden space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Category Assignment</h3>
                            <p class="mt-1 text-sm text-slate-600">Assign one or more portfolio categories, then choose which one drives the primary listing and related galleries.</p>
                        </div>
                        <a href="<?= htmlspecialchars($adminPath . '/gallery-categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">Edit Categories</a>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
                                <input type="checkbox" name="category_ids[]" value="<?= $categoryId ?>" class="mt-1 rounded border-slate-300" <?= in_array((string) $categoryId, $selectedCategoryIds, true) ? 'checked' : '' ?>>
                                <span class="block">
                                    <span class="block text-sm font-medium text-slate-900"><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars((string) ($category['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <label class="mt-5 block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Primary Category</span>
                        <select name="category_primary_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select primary category</option>
                            <?php foreach ($categories as $category): ?>
                                <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                                <option value="<?= $categoryId ?>" <?= (string) ($values['category_primary_id'] ?? '') === (string) $categoryId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>

            <div data-tab-panel="media" class="hidden space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Cover Media</h3>
                    <p class="mt-1 text-sm text-slate-600">Pick the lead visual for gallery cards and hero presentation.</p>
                    <div class="mt-4">
                        <input id="gallery-cover-media-id" type="text" name="cover_media_id" value="<?= htmlspecialchars($coverMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="gallery-cover-media-id" data-target-preview="gallery-cover-preview">Pick Cover Media</button>
                            <span id="gallery-cover-preview" class="inline-flex items-center rounded bg-white px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $coverMediaId !== '' ? htmlspecialchars($coverMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Gallery Images</h3>
                            <p class="mt-1 text-sm text-slate-600">Attach gallery imagery, mark a featured frame, and control display order.</p>
                        </div>
                        <?php if ($galleryId > 0): ?>
                            <button type="button" id="open-gallery-media-picker" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Attach Media</button>
                        <?php endif; ?>
                    </div>

                    <?php if ($galleryId > 0): ?>
                        <div id="gallery-media-manager"><?= $galleryMediaHtml ?></div>
                    <?php else: ?>
                        <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">Save the gallery first to attach and reorder gallery media.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div data-tab-panel="publish" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $statusKey => $label): ?>
                                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['status'] ?? 'draft') === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Featured</span>
                        <select name="featured" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="0" <?= (string) ($values['featured'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= (string) ($values['featured'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Event Date</span>
                        <input type="date" name="event_date" value="<?= htmlspecialchars((string) ($values['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($values['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Published At</span>
                    <input type="text" name="published_at" value="<?= htmlspecialchars((string) ($values['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="YYYY-MM-DD HH:MM:SS">
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $mode === 'create' ? 'Create Gallery' : 'Save Changes' ?></button>
            </div>
        </form>
    </div>
</section>

<?php include dirname(__DIR__, 1) . '/../components/media-picker.php'; ?>

<script>
    window.MEDIA_LIBRARY_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>,
        pickerOnly: true
    };
    window.GALLERY_ADMIN_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        galleryId: <?= json_encode($galleryId) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(asset_url('js/admin-galleries.js'), ENT_QUOTES, 'UTF-8') ?>"></script>