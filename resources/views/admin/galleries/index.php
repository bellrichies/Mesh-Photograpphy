<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'category_id' => '', 'featured' => ''];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Portfolio Galleries</h2>
                <p class="mt-1 text-sm text-slate-600">Manage showcase collections, publishing states, category placement, and featured stories.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= htmlspecialchars($adminPath . '/gallery-categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Manage Categories</a>
                <a href="<?= htmlspecialchars($adminPath . '/galleries/create', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Gallery</a>
            </div>
        </div>

        <form method="get" action="<?= htmlspecialchars($adminPath . '/galleries', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 lg:grid-cols-5">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search title, slug, client, location">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                    <option value="<?= $categoryId ?>" <?= (string) ($filters['category_id'] ?? '') === (string) $categoryId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="featured" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All feature states</option>
                <option value="1" <?= (string) ($filters['featured'] ?? '') === '1' ? 'selected' : '' ?>>Featured only</option>
                <option value="0" <?= (string) ($filters['featured'] ?? '') === '0' ? 'selected' : '' ?>>Non-featured only</option>
            </select>
            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Gallery</th>
                        <th class="px-4 py-3 font-medium">Primary Category</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Featured</th>
                        <th class="px-4 py-3 font-medium">Event</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if ($items === []): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No galleries matched the current filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($items as $item): ?>
                        <?php $galleryId = (int) ($item['id'] ?? 0); ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500">/portfolio/<?= htmlspecialchars((string) ($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['client_name'] ?? 'No client name'), ENT_QUOTES, 'UTF-8') ?><?php if ((string) ($item['location'] ?? '') !== ''): ?> • <?= htmlspecialchars((string) ($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                            </td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) ($item['primary_category_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= (string) ($item['status'] ?? '') === 'published' ? 'bg-emerald-100 text-emerald-700' : (((string) ($item['status'] ?? '') === 'archived') ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-700') ?>">
                                    <?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600"><?= (int) ($item['featured'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) ($item['event_date'] ?? ($item['published_at'] ?? 'Not scheduled')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($adminPath . '/galleries/edit/' . $galleryId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">Edit</a>
                                    <form method="post" action="<?= htmlspecialchars($adminPath . '/galleries/delete/' . $galleryId, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Archive this gallery?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="rounded border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700">Archive</button>
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
                <a href="<?= htmlspecialchars($adminPath . '/galleries?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&category_id=' . rawurlencode((string) ($filters['category_id'] ?? '')) . '&featured=' . rawurlencode((string) ($filters['featured'] ?? '')) . '&page=' . $prevPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</a>
                <a href="<?= htmlspecialchars($adminPath . '/galleries?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&category_id=' . rawurlencode((string) ($filters['category_id'] ?? '')) . '&featured=' . rawurlencode((string) ($filters['featured'] ?? '')) . '&page=' . $nextPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</section>