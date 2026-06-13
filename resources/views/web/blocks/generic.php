<?php
$block = isset($block) && is_array($block) ? $block : [];
?>
<section class="rounded-2xl border border-charcoal/10 bg-white p-6 shadow-[0_14px_48px_-24px_rgba(20,20,20,0.2)]">
    <?php if ((string) ($block['title'] ?? '') !== ''): ?><h2 class="font-display text-3xl text-charcoal"><?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2><?php endif; ?>
    <?php if ((string) ($block['body'] ?? '') !== ''): ?><div class="mt-4 whitespace-pre-line text-sm leading-7 text-charcoal/75"><?= htmlspecialchars((string) ($block['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
</section>