<?php
$values = isset($values) && is_array($values) ? $values : [];
$types = isset($types) && is_array($types) ? $types : [];
$adminPath = (string) ($adminPath ?? '/admin');
$mode = (string) ($mode ?? 'create');
$block = isset($block) && is_array($block) ? $block : null;
$id = $block !== null ? (int) ($block['id'] ?? 0) : 0;
$formAction = $mode === 'create' ? $adminPath . '/blocks/store' : $adminPath . '/blocks/update/' . $id;
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900"><?= $mode === 'create' ? 'Create Reusable Block' : 'Edit Reusable Block' ?></h2>
                <p class="mt-1 text-sm text-slate-600">Build shared content snippets that can be safely reused across the public site.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/blocks', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Blocks</a>
        </div>

        <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid gap-5 lg:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
                    <input type="text" name="name" value="<?= htmlspecialchars((string) ($values['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Block Key</span>
                    <input type="text" name="block_key" value="<?= htmlspecialchars((string) ($values['block_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="global-cta" required>
                </label>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Type</span>
                    <select name="block_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['block_type'] ?? 'snippet') === (string) $type ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst((string) $type), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'hidden' => 'Hidden'] as $status => $label): ?>
                            <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Title</span>
                <input type="text" name="title" value="<?= htmlspecialchars((string) ($values['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Body</span>
                <textarea name="body" rows="6" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($values['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">JSON Payload</span>
                <textarea name="json_payload" rows="10" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder='{"items": [], "style": "default"}'><?= htmlspecialchars((string) ($values['json_payload'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="mt-1 text-xs text-slate-500">Use for structured arrays such as trust items, announcement metadata, or CTA configuration.</p>
            </label>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white"><?= $mode === 'create' ? 'Create Block' : 'Save Changes' ?></button>
            </div>
        </form>
    </div>
</section>