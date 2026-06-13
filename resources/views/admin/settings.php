<?php
$tokenKey = (string) config('app.csrf_token_name', '_token');
$adminPath = (string) ($adminPath ?? '/admin');
$groups = isset($groups) && is_array($groups) ? $groups : [];
$activeGroup = isset($activeGroup) ? (string) $activeGroup : 'general';
$fields = isset($fields) && is_array($fields) ? $fields : [];
$values = isset($values) && is_array($values) ? $values : [];
$uploadDefaults = isset($uploadDefaults) && is_array($uploadDefaults) ? $uploadDefaults : [];
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5">
            <h2 class="text-2xl font-semibold text-slate-900">Global Settings</h2>
            <p class="mt-1 text-sm text-slate-600">Manage site-wide configuration for brand, contact details, SEO defaults, and safe operational limits.</p>
        </div>

        <div class="mb-5 flex flex-wrap gap-2">
            <?php foreach ($groups as $groupKey => $meta): ?>
                <?php
                $isActive = $groupKey === $activeGroup;
                $href = $adminPath . '/settings?group=' . rawurlencode((string) $groupKey);
                ?>
                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="rounded-full border px-4 py-2 text-sm <?= $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' ?>">
                    <?= htmlspecialchars((string) ($meta['label'] ?? ucfirst((string) $groupKey)), ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="post" action="<?= htmlspecialchars($adminPath . '/settings/update', ENT_QUOTES, 'UTF-8') ?>" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="group" value="<?= htmlspecialchars($activeGroup, ENT_QUOTES, 'UTF-8') ?>">

            <?php foreach ($fields as $fieldKey => $meta): ?>
                <?php
                $type = (string) ($meta['type'] ?? 'text');
                $label = (string) ($meta['label'] ?? $fieldKey);
                $rawValue = $values[$fieldKey] ?? '';

                if ($type === 'json-textarea') {
                    $value = is_array($rawValue) ? json_encode($rawValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $rawValue;
                } elseif ($type === 'multiselect') {
                    $value = is_array($rawValue) ? $rawValue : [];
                } else {
                    $value = is_scalar($rawValue) ? (string) $rawValue : '';
                }
                ?>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </label>

                    <?php if ($type === 'textarea' || $type === 'json-textarea'): ?>
                        <textarea id="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" rows="<?= $type === 'json-textarea' ? '6' : '4' ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800"><?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if ($type === 'json-textarea'): ?>
                            <p class="mt-1 text-xs text-slate-500">Expected valid JSON object or array.</p>
                        <?php endif; ?>
                    <?php elseif ($type === 'select'): ?>
                        <?php $options = isset($meta['options']) && is_array($meta['options']) ? $meta['options'] : []; ?>
                        <select id="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800">
                            <option value="">Select</option>
                            <?php foreach ($options as $optionKey => $optionLabel): ?>
                                <option value="<?= htmlspecialchars((string) $optionKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $value === (string) $optionKey ? 'selected' : '' ?>><?= htmlspecialchars((string) $optionLabel, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'boolean'): ?>
                        <select id="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800">
                            <option value="0" <?= $value === '0' ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= $value === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    <?php elseif ($type === 'multiselect'): ?>
                        <?php $options = isset($meta['options']) && is_array($meta['options']) ? $meta['options'] : []; ?>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <?php foreach ($options as $optionKey => $optionLabel): ?>
                                <label class="flex items-center gap-2 rounded border border-slate-300 px-3 py-2 text-sm text-slate-700">
                                    <input type="checkbox" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>[]" value="<?= htmlspecialchars((string) $optionKey, ENT_QUOTES, 'UTF-8') ?>" <?= in_array((string) $optionKey, $value, true) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars((string) $optionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($type === 'media-picker'): ?>
                        <div class="space-y-2">
                            <input id="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800" placeholder="Media ID">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-open-picker data-target-input="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" data-target-preview="preview-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>">Pick from Media Library</button>
                                <span id="preview-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded bg-slate-100 px-3 py-2 text-xs text-slate-600">Selected media ID: <?= $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : 'none' ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                        $htmlType = match ($type) {
                            'email' => 'email',
                            'number' => 'number',
                            'url' => 'url',
                            default => 'text',
                        };
                        ?>
                        <input id="settings-<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') ?>" type="<?= $htmlType ?>" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800">
                    <?php endif; ?>

                    <?php if ($activeGroup === 'uploads' && isset($uploadDefaults[$fieldKey])): ?>
                        <p class="mt-1 text-xs text-slate-500">Runtime default from configuration: <?= htmlspecialchars((string) $uploadDefaults[$fieldKey], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if ($activeGroup === 'uploads' && $fieldKey === 'allowed_mime_groups' && isset($uploadDefaults['allowed']) && is_array($uploadDefaults['allowed'])): ?>
                        <p class="mt-1 text-xs text-slate-500">Current configured MIME lists: images (<?= count((array) ($uploadDefaults['allowed']['images'] ?? [])) ?>), documents (<?= count((array) ($uploadDefaults['allowed']['documents'] ?? [])) ?>), videos (<?= count((array) ($uploadDefaults['allowed']['videos'] ?? [])) ?>)</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white">Save <?= htmlspecialchars((string) ($groups[$activeGroup]['label'] ?? 'Settings'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
        </form>
    </div>
</section>

<?php include dirname(__DIR__) . '/components/media-picker.php'; ?>

<script>
    window.MEDIA_LIBRARY_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        csrfKey: <?= json_encode($tokenKey) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>,
        pickerOnly: true
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-media.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(asset_url('js/admin-settings.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
