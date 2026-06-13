<?php
$block = isset($block) && is_array($block) ? $block : [];
?>
<section class="md:max-w-7xl mx-auto rounded-2xl bg-charcoal px-6 py-4 text-sm tracking-[0.12em] text-ivory shadow-[0_14px_48px_-24px_rgba(20,20,20,0.24)]">
    <?php if ((string) ($block['title'] ?? '') !== ''): ?><strong class="mr-2"><?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
    <span><?= htmlspecialchars((string) ($block['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
</section>