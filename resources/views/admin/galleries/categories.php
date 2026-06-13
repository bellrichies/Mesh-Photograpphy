<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => ''];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$createDefaults = isset($createDefaults) && is_array($createDefaults) ? $createDefaults : [];
$adminPath = (string) ($adminPath ?? '/admin');
$tokenKey = (string) ($tokenKey ?? '_token');
$old = app_session()->getFlash('old_input', []);
if (is_array($old) && $old !== []) {
    $createDefaults = array_merge($createDefaults, $old);
}
?>

<section class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[380px,1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <h2 class="text-xl font-semibold text-slate-900">Create Category</h2>
            <p class="mt-1 text-sm text-slate-600">Add portfolio taxonomy used for filtering, highlights, and public gallery organization.</p>

            <form method="post" action="<?= htmlspecialchars($adminPath . '/gallery-categories/store', ENT_QUOTES, 'UTF-8') ?>" class="mt-5 space-y-4 category-form" data-category-id="">
                <?= csrf_field() ?>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
                    <input type="text" name="name" value="<?= htmlspecialchars((string) ($createDefaults['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="category-name w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
                    <div class="flex gap-2">
                        <input type="text" name="slug" value="<?= htmlspecialchars((string) ($createDefaults['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="category-slug w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <button type="button" class="generate-category-slug rounded border border-slate-300 px-3 py-2 text-sm text-slate-700">Generate</button>
                    </div>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Description</span>
                    <textarea name="description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($createDefaults['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Sort Order</span>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($createDefaults['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($createDefaults['status'] ?? 'published') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Category</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Gallery Categories</h2>
                    <p class="mt-1 text-sm text-slate-600">Edit labels, ordering, and publishing states for portfolio grouping.</p>
                </div>
                <a href="<?= htmlspecialchars($adminPath . '/galleries', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Galleries</a>
            </div>

            <form method="get" action="<?= htmlspecialchars($adminPath . '/gallery-categories', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 md:grid-cols-3">
                <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search name, slug, description">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
            </form>

            <div class="space-y-4">
                <?php if ($items === []): ?>
                    <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No categories matched the current filters.</p>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php $categoryId = (int) ($item['id'] ?? 0); ?>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <form method="post" action="<?= htmlspecialchars($adminPath . '/gallery-categories/update/' . $categoryId, ENT_QUOTES, 'UTF-8') ?>" class="grid gap-4 xl:grid-cols-[1.1fr,1fr,130px,150px,auto] category-form" data-category-id="<?= $categoryId ?>">
                            <?= csrf_field() ?>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="category-name w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Slug</label>
                                <div class="flex gap-2">
                                    <input type="text" name="slug" value="<?= htmlspecialchars((string) ($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="category-slug w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                    <button type="button" class="generate-category-slug rounded border border-slate-300 px-3 py-2 text-xs text-slate-700">Generate</button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Sort</label>
                                <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($item['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Status</label>
                                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $key => $label): ?>
                                        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($item['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Save</button>
                                <button type="submit" form="delete-category-<?= $categoryId ?>" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700" onclick="return window.confirm('Delete this category?');">Delete</button>
                            </div>
                            <div class="xl:col-span-5">
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Description</label>
                                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </form>
                        <form id="delete-category-<?= $categoryId ?>" method="post" action="<?= htmlspecialchars($adminPath . '/gallery-categories/delete/' . $categoryId, ENT_QUOTES, 'UTF-8') ?>" class="hidden">
                            <?= csrf_field() ?>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="mt-5 flex items-center justify-between text-sm text-slate-600">
                <p>Page <?= (int) ($pagination['page'] ?? 1) ?> of <?= (int) ($pagination['total_pages'] ?? 1) ?> • Total <?= (int) ($pagination['total'] ?? 0) ?></p>
                <div class="flex gap-2">
                    <?php $prevPage = max(1, (int) ($pagination['page'] ?? 1) - 1); ?>
                    <?php $nextPage = min((int) ($pagination['total_pages'] ?? 1), (int) ($pagination['page'] ?? 1) + 1); ?>
                    <a href="<?= htmlspecialchars($adminPath . '/gallery-categories?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&page=' . $prevPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</a>
                    <a href="<?= htmlspecialchars($adminPath . '/gallery-categories?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&page=' . $nextPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    window.GALLERY_CATEGORY_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-galleries.js'), ENT_QUOTES, 'UTF-8') ?>"></script>