<?php
$breadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [];
$variant = (string) ($variant ?? 'dark');
if ($breadcrumbs === []) {
    return;
}
$navClass = $variant === 'light' ? 'breadcrumb-nav breadcrumb-nav-light' : 'breadcrumb-nav';
?>
<nav class="mb-6" aria-label="Breadcrumb">
    <ol class="<?= $navClass ?>">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php $isLast = $index === array_key_last($breadcrumbs); ?>
            <li class="flex items-center gap-2">
                <?php if (! $isLast && (string) ($crumb['href'] ?? '') !== ''): ?>
                    <a href="<?= htmlspecialchars(app_href((string) ($crumb['href'] ?? '#')), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($crumb['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                <?php else: ?>
                    <span class="breadcrumb-current" aria-current="page"><?= htmlspecialchars((string) ($crumb['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (! $isLast): ?><span class="breadcrumb-sep">/</span><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
