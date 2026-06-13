<?php
$section = isset($section) && is_array($section) ? $section : [];
?>
<section class="section-lg">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl rounded-3xl border border-charcoal/6 bg-white px-8 py-10 shadow-sm md:px-12 md:py-14">
            <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?><p class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <?php if ((string) ($section['title'] ?? '') !== ''): ?><h2 class="text-display-md mt-3"><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2><?php endif; ?>
            <?php if ((string) ($section['body'] ?? '') !== ''): ?><div class="mt-6 whitespace-pre-line text-body-md text-charcoal/72"><?= htmlspecialchars((string) ($section['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
    </div>
</section>