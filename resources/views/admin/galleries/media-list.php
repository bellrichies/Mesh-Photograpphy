<?php
$items = isset($items) && is_array($items) ? $items : [];
$galleryId = (int) ($galleryId ?? 0);
?>

<?php if ($items === []): ?>
    <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No gallery images attached yet.</p>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($items as $item): ?>
            <?php $mediaId = (int) ($item['id'] ?? 0); ?>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4" data-gallery-media-row data-media-id="<?= $mediaId ?>">
                <div class="grid gap-4 lg:grid-cols-[160px,1fr]">
                    <div class="h-36 overflow-hidden rounded-lg bg-slate-100">
                        <?php if ((string) ($item['file_type'] ?? '') === 'image'): ?>
                            <img src="<?= htmlspecialchars((string) ($item['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($item['title'] ?? '') !== '' ? ($item['title'] ?? '') : ($item['original_name'] ?? 'Gallery media')), ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex h-full items-center justify-center text-xs uppercase tracking-[0.2em] text-slate-500"><?= htmlspecialchars((string) ($item['file_type'] ?? 'file'), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) (($item['title'] ?? '') !== '' ? ($item['title'] ?? '') : ($item['original_name'] ?? 'Untitled')), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500">Attachment order: <?= (int) ($item['gallery_sort_order'] ?? 0) ?><?php if ((int) ($item['gallery_is_featured'] ?? 0) === 1): ?> • Featured frame<?php endif; ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="gallery-media-move rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700" data-direction="up">Move Up</button>
                                <button type="button" class="gallery-media-move rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700" data-direction="down">Move Down</button>
                            </div>
                        </div>

                        <label class="block">
                            <span class="mb-1 block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Caption</span>
                            <textarea class="gallery-media-caption w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="3"><?= htmlspecialchars((string) ($item['gallery_caption'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </label>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="gallery-media-featured rounded border-slate-300" <?= (int) ($item['gallery_is_featured'] ?? 0) === 1 ? 'checked' : '' ?>>
                                Mark as featured frame
                            </label>
                            <div class="flex gap-2">
                                <button type="button" class="gallery-media-save rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white">Save</button>
                                <button type="button" class="gallery-media-remove rounded border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>