<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'featured' => '', 'service_id' => '', 'gallery_id' => ''];
$services = isset($services) && is_array($services) ? $services : [];
$galleries = isset($galleries) && is_array($galleries) ? $galleries : [];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Testimonials</h2>
                <p class="mt-1 text-sm text-slate-600">Manage featured praise, client stories, and linked service or gallery proof points.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/testimonials/create', ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create Testimonial</a>
        </div>

        <form method="get" action="<?= htmlspecialchars($adminPath . '/testimonials', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 lg:grid-cols-6">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search client, quote, location">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="featured" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All feature states</option>
                <option value="1" <?= (string) ($filters['featured'] ?? '') === '1' ? 'selected' : '' ?>>Featured only</option>
                <option value="0" <?= (string) ($filters['featured'] ?? '') === '0' ? 'selected' : '' ?>>Non-featured only</option>
            </select>
            <select name="service_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All services</option>
                <?php foreach ($services as $service): ?>
                    <?php $serviceId = (int) ($service['id'] ?? 0); ?>
                    <option value="<?= $serviceId ?>" <?= (string) ($filters['service_id'] ?? '') === (string) $serviceId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="gallery_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All galleries</option>
                <?php foreach ($galleries as $gallery): ?>
                    <?php $galleryId = (int) ($gallery['id'] ?? 0); ?>
                    <option value="<?= $galleryId ?>" <?= (string) ($filters['gallery_id'] ?? '') === (string) $galleryId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($gallery['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Links</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Featured</th>
                        <th class="px-4 py-3 font-medium">Sort</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if ($items === []): ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No testimonials matched the current filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <?php $testimonialId = (int) ($item['id'] ?? 0); ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars((string) ($item['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if ((string) ($item['client_label'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['client_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                                <p class="mt-2 max-w-xl text-xs leading-5 text-slate-500">“<?= htmlspecialchars((string) ($item['quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?>”</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <?php if ((string) ($item['service_title'] ?? '') !== ''): ?><p>Service: <?= htmlspecialchars((string) ($item['service_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                                <?php if ((string) ($item['gallery_title'] ?? '') !== ''): ?><p class="mt-1">Gallery: <?= htmlspecialchars((string) ($item['gallery_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                                <?php if ((string) ($item['location'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            </td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= (string) ($item['status'] ?? '') === 'published' ? 'bg-emerald-100 text-emerald-700' : (((string) ($item['status'] ?? '') === 'archived') ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-700') ?>"><?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="px-4 py-4 text-slate-600"><?= (int) ($item['featured'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= (int) ($item['sort_order'] ?? 0) ?></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($adminPath . '/testimonials/edit/' . $testimonialId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">Edit</a>
                                    <form method="post" action="<?= htmlspecialchars($adminPath . '/testimonials/delete/' . $testimonialId, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return window.confirm('Archive this testimonial?');">
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
    </div>
</section>