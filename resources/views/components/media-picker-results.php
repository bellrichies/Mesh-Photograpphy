<?php
$items = isset($items) && is_array($items) ? $items : [];
?>
<?php if ($items === []): ?>
    <p class="rounded border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No media found.</p>
<?php else: ?>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($items as $item): ?>
            <?php
            $mediaId = (int) ($item['id'] ?? 0);
            $name = (string) (($item['title'] ?? '') !== '' ? $item['title'] : ($item['original_name'] ?? 'Untitled'));
            $url = app_media_url((string) ($item['directory'] ?? ''), (string) ($item['stored_name'] ?? ''));
            $metaLabel = trim((string) ($item['file_type'] ?? 'file'));
            if (isset($item['size_bytes']) && (int) $item['size_bytes'] > 0) {
                $metaLabel .= ' | ' . number_format(((int) $item['size_bytes']) / 1024, 0) . ' KB';
            }
            ?>
            <button type="button" class="group rounded-xl border border-slate-200 bg-white p-2.5 text-left transition hover:border-slate-400 hover:shadow-sm" data-picker-select data-media-id="<?= $mediaId ?>" data-media-url="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" data-media-name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-2 h-24 overflow-hidden rounded-lg bg-slate-100">
                    <?php if ((string) ($item['file_type'] ?? '') === 'image'): ?>
                        <img src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-full items-center justify-center text-xs uppercase tracking-[0.2em] text-slate-500"><?= htmlspecialchars((string) ($item['file_type'] ?? 'file'), ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <p class="line-clamp-2 text-xs font-semibold leading-5 text-slate-700"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mt-1 truncate text-[11px] text-slate-500"><?= htmlspecialchars($metaLabel, ENT_QUOTES, 'UTF-8') ?></p>
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
