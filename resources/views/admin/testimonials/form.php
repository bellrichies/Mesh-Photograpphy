<?php
$testimonial = isset($testimonial) && is_array($testimonial) ? $testimonial : null;
$values = isset($values) && is_array($values) ? $values : [];
$services = isset($services) && is_array($services) ? $services : [];
$galleries = isset($galleries) && is_array($galleries) ? $galleries : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$testimonialId = $testimonial !== null ? (int) ($testimonial['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/testimonials/store' : $adminPath . '/testimonials/update/' . $testimonialId;
$tokenKey = (string) ($tokenKey ?? '_token');
$portraitMediaId = is_scalar($values['portrait_media_id'] ?? '') ? (string) ($values['portrait_media_id'] ?? '') : '';
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Testimonial' : 'Edit Testimonial' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Capture premium client praise, richer long-form stories, and links back to service or gallery proof.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/testimonials', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Testimonials</a>
        </div>

        <div class="mb-5 flex flex-wrap gap-2" data-tabs-nav>
            <button type="button" class="rounded-full border border-slate-900 bg-slate-900 px-4 py-2 text-sm text-white" data-tab-target="content">Content</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="relationships">Relationships</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="media">Media</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="publish">Publish Settings</button>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div data-tab-panel="content" class="space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Client Name</span>
                        <input type="text" name="client_name" value="<?= htmlspecialchars((string) ($values['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Client Label</span>
                        <input type="text" name="client_label" value="<?= htmlspecialchars((string) ($values['client_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Wedding couple, Brand founder, Family session">
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Quote</span>
                    <textarea name="quote" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required><?= htmlspecialchars((string) ($values['quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Long Form Story</span>
                    <textarea name="long_form_story" rows="10" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional editorial client story for richer public storytelling later."><?= htmlspecialchars((string) ($values['long_form_story'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <div data-tab-panel="relationships" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Linked Service</span>
                        <select name="service_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">No linked service</option>
                            <?php foreach ($services as $service): ?>
                                <?php $serviceId = (int) ($service['id'] ?? 0); ?>
                                <option value="<?= $serviceId ?>" <?= (string) ($values['service_id'] ?? '') === (string) $serviceId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Linked Gallery</span>
                        <select name="gallery_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">No linked gallery</option>
                            <?php foreach ($galleries as $gallery): ?>
                                <?php $galleryId = (int) ($gallery['id'] ?? 0); ?>
                                <option value="<?= $galleryId ?>" <?= (string) ($values['gallery_id'] ?? '') === (string) $galleryId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($gallery['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Rating</span>
                        <select name="rating" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">No rating</option>
                            <?php foreach ([1, 2, 3, 4, 5] as $rating): ?>
                                <option value="<?= $rating ?>" <?= (string) ($values['rating'] ?? '') === (string) $rating ? 'selected' : '' ?>><?= $rating ?> / 5</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Event Date</span>
                        <input type="date" name="event_date" value="<?= htmlspecialchars((string) ($values['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Location</span>
                        <input type="text" name="location" value="<?= htmlspecialchars((string) ($values['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>
            </div>

            <div data-tab-panel="media" class="hidden space-y-5">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Portrait Media</label>
                    <input id="testimonial-portrait-media-id" type="text" name="portrait_media_id" value="<?= htmlspecialchars($portraitMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="testimonial-portrait-media-id" data-target-preview="testimonial-portrait-preview">Pick Portrait Media</button>
                        <span id="testimonial-portrait-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $portraitMediaId !== '' ? htmlspecialchars($portraitMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                    </div>
                </div>
            </div>

            <div data-tab-panel="publish" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-3">
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
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($values['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $mode === 'create' ? 'Create Testimonial' : 'Save Changes' ?></button>
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
<script src="<?= htmlspecialchars(asset_url('js/admin-testimonials.js'), ENT_QUOTES, 'UTF-8') ?>"></script>