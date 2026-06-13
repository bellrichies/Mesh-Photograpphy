<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => ''];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$createDefaults = isset($createDefaults) && is_array($createDefaults) ? $createDefaults : [];
$adminPath = (string) ($adminPath ?? '/admin');
$old = app_session()->getFlash('old_input', []);
if (is_array($old) && $old !== []) {
    $createDefaults = array_merge($createDefaults, $old);
}
?>

<section class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[380px,1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <h2 class="text-xl font-semibold text-slate-900">Create Blog Tag</h2>
            <p class="mt-1 text-sm text-slate-600">Add flexible tag labels for post discovery, overlap logic, and search facets.</p>

            <form method="post" action="<?= htmlspecialchars($adminPath . '/blog/tags/store', ENT_QUOTES, 'UTF-8') ?>" class="mt-5 space-y-4 blog-tag-form" data-tag-id="">
                <?= csrf_field() ?>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
                    <input type="text" name="name" value="<?= htmlspecialchars((string) ($createDefaults['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="blog-tag-name w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
                    <div class="flex gap-2">
                        <input type="text" name="slug" value="<?= htmlspecialchars((string) ($createDefaults['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="blog-tag-slug w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <button type="button" class="generate-blog-tag-slug rounded border border-slate-300 px-3 py-2 text-sm text-slate-700">Generate</button>
                    </div>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Description</span>
                    <textarea name="description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($createDefaults['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Tag</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Blog Tags</h2>
                    <p class="mt-1 text-sm text-slate-600">Maintain reusable topic tags for post relationships and search.</p>
                </div>
                <a href="<?= htmlspecialchars($adminPath . '/blog/posts', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Posts</a>
            </div>

            <form method="get" action="<?= htmlspecialchars($adminPath . '/blog/tags', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 md:grid-cols-[1fr,auto]">
                <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search name, slug, description">
                <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
            </form>

            <div class="space-y-4">
                <?php if ($items === []): ?>
                    <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No blog tags matched the current filters.</p>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php $tagId = (int) ($item['id'] ?? 0); ?>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <form method="post" action="<?= htmlspecialchars($adminPath . '/blog/tags/update/' . $tagId, ENT_QUOTES, 'UTF-8') ?>" class="grid gap-4 xl:grid-cols-[1.1fr,1fr,auto] blog-tag-form" data-tag-id="<?= $tagId ?>">
                            <?= csrf_field() ?>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="blog-tag-name w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Slug</label>
                                <div class="flex gap-2">
                                    <input type="text" name="slug" value="<?= htmlspecialchars((string) ($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="blog-tag-slug w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                    <button type="button" class="generate-blog-tag-slug rounded border border-slate-300 px-3 py-2 text-xs text-slate-700">Generate</button>
                                </div>
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Save</button>
                                <button type="submit" form="delete-blog-tag-<?= $tagId ?>" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700" onclick="return window.confirm('Delete this blog tag?');">Delete</button>
                            </div>
                            <div class="xl:col-span-3">
                                <label class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Description</label>
                                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </form>
                        <form id="delete-blog-tag-<?= $tagId ?>" method="post" action="<?= htmlspecialchars($adminPath . '/blog/tags/delete/' . $tagId, ENT_QUOTES, 'UTF-8') ?>" class="hidden">
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
                    <a href="<?= htmlspecialchars($adminPath . '/blog/tags?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&page=' . $prevPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</a>
                    <a href="<?= htmlspecialchars($adminPath . '/blog/tags?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&page=' . $nextPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    window.BLOG_TAG_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-blog.js'), ENT_QUOTES, 'UTF-8') ?>"></script>