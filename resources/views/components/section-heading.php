<?php
$eyebrow = (string) ($eyebrow ?? '');
$title = (string) ($title ?? '');
$description = (string) ($description ?? '');
$align = (string) ($align ?? 'left');
$classes = (string) ($classes ?? '');
$alignmentClass = $align === 'center' ? 'text-center mx-auto' : '';
?>
<div class="<?= htmlspecialchars(trim('max-w-3xl ' . $alignmentClass . ' ' . $classes), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($eyebrow !== ''): ?><p class="section-label"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($title !== ''): ?><h2 class="mt-3 text-display-md"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2><?php endif; ?>
    <?php if ($description !== ''): ?><p class="section-subtitle"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
</div>