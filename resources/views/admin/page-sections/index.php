<?php
$page = isset($page) && is_array($page) ? $page : [];
$sections = isset($sections) && is_array($sections) ? $sections : [];
$sectionTypes = isset($sectionTypes) && is_array($sectionTypes) ? $sectionTypes : [];
$adminPath = (string) ($adminPath ?? '/admin');
$editingSectionId = isset($editingSectionId) ? (int) $editingSectionId : 0;
$pageId = (int) ($page['id'] ?? 0);

$editingSection = null;
foreach ($sections as $sectionRow) {
    if ((int) ($sectionRow['id'] ?? 0) === $editingSectionId) {
        $editingSection = $sectionRow;
        break;
    }
}

$formMode = is_array($editingSection) ? 'edit' : 'create';
$formAction = $formMode === 'edit'
    ? $adminPath . '/pages/' . $pageId . '/sections/update/' . (int) ($editingSection['id'] ?? 0)
    : $adminPath . '/pages/' . $pageId . '/sections/store';

$values = is_array($editingSection) ? $editingSection : [
    'section_key' => '',
    'section_type' => 'hero',
    'title' => '',
    'subtitle' => '',
    'body' => '',
    'cta_label' => '',
    'cta_url' => '',
    'media_id' => '',
    'json_payload' => '',
    'sort_order' => count($sections) + 1,
    'status' => 'draft',
];

$jsonPayloadValue = $values['json_payload'] ?? '';
if (is_string($jsonPayloadValue) && $jsonPayloadValue !== '') {
    $decodedJson = json_decode($jsonPayloadValue, true);
    if (is_array($decodedJson)) {
        $jsonPayloadValue = json_encode($decodedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
?>

<section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Sections for <?= htmlspecialchars((string) ($page['title'] ?? 'Page'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-1 text-sm text-slate-600">Compose the page from structured content blocks and control order, visibility, and rendering type.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= htmlspecialchars($adminPath . '/pages/edit/' . $pageId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Page</a>
                <a href="<?= htmlspecialchars($adminPath . '/pages', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">All Pages</a>
            </div>
        </div>

        <div id="section-list" class="space-y-3">
            <?php if ($sections === []): ?>
                <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No sections added yet.</p>
            <?php endif; ?>

            <?php foreach ($sections as $section): ?>
                <?php
                $sectionId = (int) ($section['id'] ?? 0);
                $status = (string) ($section['status'] ?? 'draft');
                $toggleStatus = match ($status) {
                    'published' => 'hidden',
                    'hidden' => 'draft',
                    default => 'published',
                };
                ?>
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4" data-section-card data-section-id="<?= $sectionId ?>">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) (($section['title'] ?? '') !== '' ? $section['title'] : ($section['section_key'] ?? 'Untitled')), ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="inline-flex rounded-full bg-slate-200 px-2 py-1 text-[11px] font-medium uppercase tracking-[0.16em] text-slate-700"><?= htmlspecialchars((string) ($section['section_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium <?= $status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($status === 'hidden' ? 'bg-slate-300 text-slate-700' : 'bg-amber-100 text-amber-700') ?>" data-status-label><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Key: <?= htmlspecialchars((string) ($section['section_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?> • Sort: <span data-sort-order><?= (int) ($section['sort_order'] ?? 0) ?></span></p>
                            <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?>
                                <p class="mt-2 text-sm text-slate-600"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="rounded border border-slate-300 px-3 py-1.5 text-xs text-slate-700" data-section-reorder data-direction="up">Up</button>
                            <button type="button" class="rounded border border-slate-300 px-3 py-1.5 text-xs text-slate-700" data-section-reorder data-direction="down">Down</button>
                            <button type="button" class="rounded border border-slate-300 px-3 py-1.5 text-xs text-slate-700" data-section-status data-status="<?= htmlspecialchars($toggleStatus, ENT_QUOTES, 'UTF-8') ?>">Set <?= htmlspecialchars(ucfirst($toggleStatus), ENT_QUOTES, 'UTF-8') ?></button>
                            <a href="<?= htmlspecialchars($adminPath . '/pages/' . $pageId . '/sections?edit=' . $sectionId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs text-slate-700">Edit</a>
                            <form method="post" action="<?= htmlspecialchars($adminPath . '/pages/' . $pageId . '/sections/delete/' . $sectionId, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Remove this section?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="rounded border border-rose-300 px-3 py-1.5 text-xs text-rose-700">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5">
            <h2 class="text-xl font-semibold text-slate-900"><?= $formMode === 'edit' ? 'Edit Section' : 'Add Section' ?></h2>
            <p class="mt-1 text-sm text-slate-600">Use structured fields for layout blocks and keep advanced configuration inside JSON payload only when needed.</p>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Section Key</span>
                    <input type="text" name="section_key" value="<?= htmlspecialchars((string) ($values['section_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="hero-intro">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Section Type</span>
                    <select name="section_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach ($sectionTypes as $sectionType): ?>
                            <option value="<?= htmlspecialchars((string) $sectionType, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['section_type'] ?? 'hero') === (string) $sectionType ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('-', ' ', (string) $sectionType)), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                <input type="text" name="title" value="<?= htmlspecialchars((string) ($values['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Subtitle</span>
                <input type="text" name="subtitle" value="<?= htmlspecialchars((string) ($values['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Body</span>
                <textarea name="body" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($values['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">CTA Label</span>
                    <input type="text" name="cta_label" value="<?= htmlspecialchars((string) ($values['cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">CTA URL</span>
                    <input type="url" name="cta_url" value="<?= htmlspecialchars((string) ($values['cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Section Media</label>
                <?php $mediaId = is_scalar($values['media_id'] ?? '') ? (string) ($values['media_id'] ?? '') : ''; ?>
                <input id="section-media-id" type="text" name="media_id" value="<?= htmlspecialchars($mediaId, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Media ID">
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="section-media-id" data-target-preview="section-media-preview">Pick Section Media</button>
                    <span id="section-media-preview" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $mediaId !== '' ? htmlspecialchars($mediaId, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                </div>
            </div>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">JSON Payload</span>
                <textarea name="json_payload" rows="8" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder='{"items": [], "notes": "Section-specific configuration"}'><?= htmlspecialchars((string) $jsonPayloadValue, ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="mt-1 text-xs text-slate-500">Use for structured arrays like FAQ items, gallery references, or split-content side data.</p>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                    <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($values['sort_order'] ?? count($sections) + 1), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'hidden' => 'Hidden'] as $statusKey => $statusLabel): ?>
                            <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['status'] ?? 'draft') === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <?php if ($formMode === 'edit'): ?>
                    <a href="<?= htmlspecialchars($adminPath . '/pages/' . $pageId . '/sections', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">New Section</a>
                <?php endif; ?>
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $formMode === 'edit' ? 'Update Section' : 'Add Section' ?></button>
            </div>
        </form>
    </div>
</section>

<?php include dirname(__DIR__, 1) . '/../components/media-picker.php'; ?>

<script>
    window.MEDIA_LIBRARY_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        csrfKey: <?= json_encode((string) config('app.csrf_token_name', '_token')) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>,
        pickerOnly: true
    };
    window.PAGE_SECTION_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        pageId: <?= json_encode($pageId) ?>,
        csrfKey: <?= json_encode((string) config('app.csrf_token_name', '_token')) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(asset_url('js/admin-page-sections.js'), ENT_QUOTES, 'UTF-8') ?>"></script>