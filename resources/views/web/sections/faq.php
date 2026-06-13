<?php
$section = isset($section) && is_array($section) ? $section : [];
$payload = isset($section['payload']) && is_array($section['payload'])
    ? $section['payload']
    : json_decode((string) ($section['json_payload'] ?? ''), true);
$items = is_array($payload) && isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
?>
<section class="section-xl">
    <div class="container">
        <div class="reveal section-header section-header-center">
            <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?>
                <p class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="section-label">FAQ</p>
            <?php endif; ?>
            <h2 class="text-display-md mt-3"><?= htmlspecialchars((string) ($section['title'] ?? 'Frequently Asked Questions'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="mx-auto max-w-3xl space-y-4">
            <?php if ($items === []): ?>
                <p class="py-8 text-center text-sm text-charcoal/60">Add FAQ items with question and answer keys in the JSON payload.</p>
            <?php endif; ?>
            <?php foreach ($items as $index => $item): ?>
                <details class="reveal reveal-delay-<?= min($index + 1, 4) ?> group rounded-2xl border border-charcoal/8 bg-white shadow-sm transition-shadow hover:shadow-md">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-5 text-base font-medium text-charcoal">
                        <span><?= htmlspecialchars((string) ($item['question'] ?? 'Question'), ENT_QUOTES, 'UTF-8') ?></span>
                        <svg class="h-5 w-5 shrink-0 text-bronze transition-transform duration-300 group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </summary>
                    <div class="px-6 pb-6 text-body-sm text-charcoal/70 leading-relaxed"><?= htmlspecialchars((string) ($item['answer'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
