<?php
$service = isset($service) && is_array($service) ? $service : null;
$serviceValues = isset($serviceValues) && is_array($serviceValues) ? $serviceValues : [];
$seoValues = isset($seoValues) && is_array($seoValues) ? $seoValues : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$serviceId = $service !== null ? (int) ($service['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/services/store' : $adminPath . '/services/update/' . $serviceId;
$tokenKey = (string) ($tokenKey ?? '_token');
$coverMediaId = is_scalar($serviceValues['cover_media_id'] ?? '') ? (string) ($serviceValues['cover_media_id'] ?? '') : '';
$ogImageMediaId = is_scalar($seoValues['og_image'] ?? '') ? (string) ($seoValues['og_image'] ?? '') : '';
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Service' : 'Edit Service' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Shape public service offers with rich copy, featured visuals, pricing language, and SEO metadata.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/services', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Services</a>
        </div>

        <div class="mb-5 flex flex-wrap gap-2" data-tabs-nav>
            <button type="button" class="rounded-full border border-slate-900 bg-slate-900 px-4 py-2 text-sm text-white" data-tab-target="content">Content</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="media">Media</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="seo">SEO</button>
            <button type="button" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700" data-tab-target="publish">Publish Settings</button>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div data-tab-panel="content" class="space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                        <input id="service-title" type="text" name="title" value="<?= htmlspecialchars((string) ($serviceValues['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
                        <div class="flex gap-2">
                            <input id="service-slug" type="text" name="slug" value="<?= htmlspecialchars((string) ($serviceValues['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <button type="button" id="generate-service-slug" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">Generate</button>
                        </div>
                        <p id="service-slug-feedback" class="mt-1 text-xs text-slate-500">Slug will be normalized and kept unique.</p>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Short Description</span>
                        <textarea name="short_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($serviceValues['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Price Display</span>
                        <input type="text" name="price_display" value="<?= htmlspecialchars((string) ($serviceValues['price_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Starting at $2,500 or Custom proposal">
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Full Description</span>
                    <textarea name="full_description" rows="16" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Describe what is included, positioning, process, and experience."><?= htmlspecialchars((string) ($serviceValues['full_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <div data-tab-panel="media" class="hidden space-y-5">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Cover Media</label>
                    <input id="service-cover-media-id" type="text" name="cover_media_id" value="<?= htmlspecialchars($coverMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="service-cover-media-id" data-target-preview="service-cover-preview">Pick Cover Media</button>
                        <span id="service-cover-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $coverMediaId !== '' ? htmlspecialchars($coverMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                    </div>
                </div>
            </div>

            <div data-tab-panel="seo" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Meta Title</span>
                        <input type="text" name="meta_title" value="<?= htmlspecialchars((string) ($seoValues['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">OG Title</span>
                        <input type="text" name="og_title" value="<?= htmlspecialchars((string) ($seoValues['og_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Meta Description</span>
                        <textarea name="meta_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($seoValues['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">OG Description</span>
                        <textarea name="og_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($seoValues['og_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Canonical URL</span>
                    <input type="url" name="canonical_url" value="<?= htmlspecialchars((string) ($seoValues['canonical_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">OG Image</label>
                    <input id="service-og-image-media-id" type="text" name="og_image_media_id" value="<?= htmlspecialchars($ogImageMediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="service-og-image-media-id" data-target-preview="service-og-image-preview">Pick OG Image</button>
                        <span id="service-og-image-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $ogImageMediaId !== '' ? htmlspecialchars($ogImageMediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                    </div>
                </div>
            </div>

            <div data-tab-panel="publish" class="hidden space-y-5">
                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $statusKey => $label): ?>
                                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($serviceValues['status'] ?? 'draft') === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Featured</span>
                        <select name="featured" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="0" <?= (string) ($serviceValues['featured'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= (string) ($serviceValues['featured'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($serviceValues['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <div class="grid gap-5 lg:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">Robots Index</span>
                            <select name="robots_index" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="1" <?= (string) ($seoValues['robots_index'] ?? '1') === '1' ? 'selected' : '' ?>>Index</option>
                                <option value="0" <?= (string) ($seoValues['robots_index'] ?? '1') === '0' ? 'selected' : '' ?>>No Index</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">Robots Follow</span>
                            <select name="robots_follow" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="1" <?= (string) ($seoValues['robots_follow'] ?? '1') === '1' ? 'selected' : '' ?>>Follow</option>
                                <option value="0" <?= (string) ($seoValues['robots_follow'] ?? '1') === '0' ? 'selected' : '' ?>>No Follow</option>
                            </select>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $mode === 'create' ? 'Create Service' : 'Save Changes' ?></button>
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
    window.SERVICE_EDITOR_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        serviceId: <?= json_encode($serviceId) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(asset_url('js/admin-services.js'), ENT_QUOTES, 'UTF-8') ?>"></script>