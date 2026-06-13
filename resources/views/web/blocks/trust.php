<?php
$block = isset($block) && is_array($block) ? $block : [];
$payload = json_decode((string) ($block['json_payload'] ?? ''), true);
$items = is_array($payload) && isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
?>
<section class="md:max-w-7xl mx-auto rounded-2xl border border-charcoal/10 bg-white p-6 shadow-[0_14px_48px_-24px_rgba(20,20,20,0.2)]">
    <h2 class="font-display text-3xl text-charcoal"><?= htmlspecialchars((string) ($block['title'] ?? 'Trust Signals'), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="mt-5 grid gap-4 md:grid-cols-3">
        <?php foreach ($items as $item): ?>
            <article class="rounded-xl bg-[#f3ede5] p-4">
                <h3 class="font-medium text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? 'Item'), ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if ((string) ($item['body'] ?? '') !== ''): ?><p class="mt-2 text-sm leading-6 text-charcoal/70"><?= htmlspecialchars((string) ($item['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>