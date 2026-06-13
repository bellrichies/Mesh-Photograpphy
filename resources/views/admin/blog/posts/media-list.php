<?php
$items = isset($items) && is_array($items) ? $items : [];
$postId = (int) ($postId ?? 0);
?>

<?php if ($items === []): ?>
    <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No supporting media attached yet.</p>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($items as $item): ?>
            <?php $mediaId = (int) ($item['media_id'] ?? 0); ?>
            <article class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[110px,1fr,auto]" data-blog-post-media-row data-media-id="<?= $mediaId ?>">
                <div class="h-24 overflow-hidden rounded-lg bg-slate-200">
                    <?php if ((string) ($item['url'] ?? '') !== ''): ?>
                        <img src="<?= htmlspecialchars((string) ($item['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($item['alt_text'] ?? '') !== '' ? ($item['alt_text'] ?? '') : ($item['title'] ?? 'Media preview')), ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-full items-center justify-center text-xs uppercase tracking-[0.18em] text-slate-500">Media</div>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars((string) (($item['title'] ?? '') !== '' ? ($item['title'] ?? '') : ($item['original_name'] ?? 'Untitled media')), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-1 text-xs text-slate-500">Media ID <?= $mediaId ?> • Sort <?= (int) ($item['sort_order'] ?? 0) ?></p>
                    <textarea class="blog-post-media-caption mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="3" placeholder="Optional caption for this asset."><?= htmlspecialchars((string) ($item['caption'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="flex flex-wrap items-start gap-2 lg:w-40 lg:flex-col">
                    <button type="button" class="blog-post-media-save rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700">Save</button>
                    <button type="button" class="blog-post-media-move rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-direction="up">Move Up</button>
                    <button type="button" class="blog-post-media-move rounded border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700" data-direction="down">Move Down</button>
                    <button type="button" class="blog-post-media-remove rounded border border-rose-300 px-3 py-2 text-xs font-medium text-rose-700">Remove</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>