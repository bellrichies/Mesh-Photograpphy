<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'author_id' => '', 'category_id' => '', 'featured' => '', 'date_from' => '', 'date_to' => ''];
$authors = isset($authors) && is_array($authors) ? $authors : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Blog Posts</h2>
                <p class="mt-1 text-sm text-slate-600">Manage editorial posts, publication timing, taxonomy, and featured placement.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= htmlspecialchars($adminPath . '/blog/categories', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Categories</a>
                <a href="<?= htmlspecialchars($adminPath . '/blog/tags', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Tags</a>
                <a href="<?= htmlspecialchars($adminPath . '/blog/posts/create', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Post</a>
            </div>
        </div>

        <form method="get" action="<?= htmlspecialchars($adminPath . '/blog/posts', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 xl:grid-cols-7">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search title, slug, excerpt, SEO summary">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="author_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All authors</option>
                <?php foreach ($authors as $author): ?>
                    <?php $authorId = (int) ($author['id'] ?? 0); ?>
                    <option value="<?= $authorId ?>" <?= (string) ($filters['author_id'] ?? '') === (string) $authorId ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) (($author['first_name'] ?? '') . ' ' . ($author['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></option>
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
            <input type="date" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="xl:col-span-7 rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Post</th>
                        <th class="px-4 py-3 font-medium">Author</th>
                        <th class="px-4 py-3 font-medium">Categories</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Featured</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if ($items === []): ?>
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No blog posts matched the current filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <?php $postId = (int) ($item['id'] ?? 0); ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500">/blog/<?= htmlspecialchars((string) ($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if ((string) ($item['excerpt'] ?? '') !== ''): ?><p class="mt-2 max-w-xl text-xs leading-5 text-slate-500"><?= htmlspecialchars((string) ($item['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars(trim((string) (($item['author_first_name'] ?? '') . ' ' . ($item['author_last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) (($item['category_names'] ?? '') !== '' ? ($item['category_names'] ?? '') : 'Uncategorized'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= (string) ($item['status'] ?? '') === 'published' ? 'bg-emerald-100 text-emerald-700' : (((string) ($item['status'] ?? '') === 'archived') ? 'bg-slate-200 text-slate-700' : (((string) ($item['status'] ?? '') === 'scheduled') ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700')) ?>"><?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="px-4 py-4 text-slate-600"><?= (int) ($item['is_featured'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) (($item['published_at'] ?? '') !== '' ? ($item['published_at'] ?? '') : (($item['scheduled_at'] ?? '') !== '' ? ($item['scheduled_at'] ?? '') : ($item['created_at'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($adminPath . '/blog/posts/edit/' . $postId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">Edit</a>
                                    <form method="post" action="<?= htmlspecialchars($adminPath . '/blog/posts/delete/' . $postId, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Archive this blog post?');">
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
                <a href="<?= htmlspecialchars($adminPath . '/blog/posts?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&author_id=' . rawurlencode((string) ($filters['author_id'] ?? '')) . '&category_id=' . rawurlencode((string) ($filters['category_id'] ?? '')) . '&featured=' . rawurlencode((string) ($filters['featured'] ?? '')) . '&date_from=' . rawurlencode((string) ($filters['date_from'] ?? '')) . '&date_to=' . rawurlencode((string) ($filters['date_to'] ?? '')) . '&page=' . $prevPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</a>
                <a href="<?= htmlspecialchars($adminPath . '/blog/posts?q=' . rawurlencode((string) ($filters['q'] ?? '')) . '&status=' . rawurlencode((string) ($filters['status'] ?? '')) . '&author_id=' . rawurlencode((string) ($filters['author_id'] ?? '')) . '&category_id=' . rawurlencode((string) ($filters['category_id'] ?? '')) . '&featured=' . rawurlencode((string) ($filters['featured'] ?? '')) . '&date_from=' . rawurlencode((string) ($filters['date_from'] ?? '')) . '&date_to=' . rawurlencode((string) ($filters['date_to'] ?? '')) . '&page=' . $nextPage, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</section>