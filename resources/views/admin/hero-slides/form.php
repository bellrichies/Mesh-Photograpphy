<?php
$slide = isset($slide) && is_array($slide) ? $slide : null;
$values = isset($values) && is_array($values) ? $values : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$slideId = $slide !== null ? (int) ($slide['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/hero-slides/store' : $adminPath . '/hero-slides/update/' . $slideId;
$tokenKey = (string) ($tokenKey ?? '_token');
$imageMediaId = is_scalar($values['image_media_id'] ?? '') ? (string) ($values['image_media_id'] ?? '') : '';
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Hero Slide' : 'Edit Hero Slide' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Control the homepage slider content, call-to-actions, ordering, and publication state from one place.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/hero-slides', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Slides</a>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div class="grid gap-5 lg:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                    <input type="text" name="title" value="<?= htmlspecialchars((string) ($values['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Eyebrow / Subtitle</span>
                    <input type="text" name="subtitle" value="<?= htmlspecialchars((string) ($values['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Luxury Wedding Storytelling">
                </label>
            </div>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Description</span>
                <textarea name="description" rows="6" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Short supporting copy for the slide."><?= htmlspecialchars((string) ($values['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Slide Image</label>
                    <input id="hero-slide-image-media-id" type="text" name="image_media_id" value="<?= htmlspecialchars($imageMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID" required>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="hero-slide-image-media-id" data-target-preview="hero-slide-image-preview">Pick Slide Image</button>
                        <span id="hero-slide-image-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $imageMediaId !== '' ? htmlspecialchars($imageMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                    </div>
                </div>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Image Alt Text</span>
                    <input type="text" name="image_alt_text" value="<?= htmlspecialchars((string) ($values['image_alt_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional override for accessible alt text">
                </label>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Primary CTA</h3>
                    <div class="mt-3 grid gap-4">
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">Label</span>
                            <input type="text" name="primary_cta_label" value="<?= htmlspecialchars((string) ($values['primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Book the Experience">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">URL</span>
                            <input type="text" name="primary_cta_url" value="<?= htmlspecialchars((string) ($values['primary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="/contact or https://example.com">
                        </label>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Secondary CTA</h3>
                    <div class="mt-3 grid gap-4">
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">Label</span>
                            <input type="text" name="secondary_cta_label" value="<?= htmlspecialchars((string) ($values['secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="View Portfolio">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">URL</span>
                            <input type="text" name="secondary_cta_url" value="<?= htmlspecialchars((string) ($values['secondary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="/portfolio or https://example.com">
                        </label>
                    </div>
                </div>
            </div>

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
                    <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                    <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($values['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $mode === 'create' ? 'Create Slide' : 'Save Changes' ?></button>
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
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
