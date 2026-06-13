<?php
$block = isset($block) && is_array($block) ? $block : [];
$payload = json_decode((string) ($block['json_payload'] ?? ''), true);
$ctaLabel = is_array($payload) ? (string) ($payload['cta_label'] ?? 'Start a Conversation') : 'Start a Conversation';
$ctaUrl = app_href(is_array($payload) ? (string) ($payload['cta_url'] ?? '#') : '#');
?>
<section class="md:max-w-7xl mx-auto rounded-2xl mb-12 border border-bronze/25 bg-[#f0e6db] px-8 py-10 text-charcoal shadow-[0_14px_48px_-24px_rgba(20,20,20,0.24)]">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-bronze">Shared CTA</p>
            <h2 class="mt-3 font-display text-4xl"><?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ((string) ($block['body'] ?? '') !== ''): ?><p class="mt-4 max-w-2xl text-base leading-8 text-charcoal/75"><?= htmlspecialchars((string) ($block['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        </div>
        <a href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-full bg-charcoal px-5 py-3 text-sm font-medium text-ivory"><?= htmlspecialchars($ctaLabel, ENT_QUOTES, 'UTF-8') ?></a>
    </div>
</section>
