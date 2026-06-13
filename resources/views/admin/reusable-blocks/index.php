<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'type' => ''];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$types = isset($types) && is_array($types) ? $types : [];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Reusable Blocks</h2>
                <p class="mt-1 text-sm text-slate-600">Manage shared content snippets for CTA banners, footer notes, trust strips, and other repeated content patterns.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/blocks/create', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Block</a>
        </div>

        <form method="get" action="<?= htmlspecialchars($adminPath . '/blocks', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 md:grid-cols-4">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search name, key, title">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'hidden' => 'Hidden'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All types</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['type'] ?? '') === (string) $type ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst((string) $type), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Block</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Updated</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if ($items === []): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No reusable blocks matched the current filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <?php $id = (int) ($item['id'] ?? 0); ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500">Key: <?= htmlspecialchars((string) ($item['block_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if ((string) ($item['title'] ?? '') !== ''): ?><p class="mt-2 text-xs text-slate-600"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) ($item['block_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= (string) ($item['status'] ?? '') === 'published' ? 'bg-emerald-100 text-emerald-700' : (((string) ($item['status'] ?? '') === 'hidden') ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-700') ?>"><?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) ($item['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($adminPath . '/blocks/edit/' . $id, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">Edit</a>
                                    <form method="post" action="<?= htmlspecialchars($adminPath . '/blocks/delete/' . $id, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Delete this reusable block?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="rounded border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex items-center justify-between text-sm text-slate-600">
            <p>Page <?= (int) ($pagination['page'] ?? 1) ?> of <?= (int) ($pagination['total_pages'] ?? 1) ?> • Total <?= (int) ($pagination['total'] ?? 0) ?></p>
            <div class="flex gap-2">
                <?php $prevPage = max(1, (int) ($pagination['page'] ?? 1) - 1); ?>
                <?php $nextPage = min((int) ($pagination['total_pages'] ?? 1), (int) ($pagination['page'] ?? 1) + 1); ?>
                <a href="<?= htmlspecialchars($adminPath . '/blocks?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&type=' . rawurlencode((string) ($filters['type'] ?? '')) . '&page=' . $prevPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</a>
                <a href="<?= htmlspecialchars($adminPath . '/blocks?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&type=' . rawurlencode((string) ($filters['type'] ?? '')) . '&page=' . $nextPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</section>