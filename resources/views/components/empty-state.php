<?php
$title = (string) ($title ?? 'Nothing to show yet.');
$description = (string) ($description ?? '');
$actionHref = (string) ($actionHref ?? '');
$actionLabel = (string) ($actionLabel ?? '');
$classes = (string) ($classes ?? '');
?>
<section class="empty-state <?= htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') ?>">
    <p class="section-label">Empty State</p>
    <h2 class="empty-state-title mt-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
    <?php if ($description !== ''): ?><p class="empty-state-desc"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($actionHref !== '' && $actionLabel !== ''): ?><a href="<?= htmlspecialchars($actionHref, ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary mt-4"><?= htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
</section>