<?php
$section = isset($section) && is_array($section) ? $section : [];
$payload = isset($section['payload']) && is_array($section['payload'])
    ? $section['payload']
    : json_decode((string) ($section['json_payload'] ?? ''), true);
$sideTitle = is_array($payload) ? (string) ($payload['side_title'] ?? '') : '';
$sideBody = is_array($payload) ? (string) ($payload['side_body'] ?? '') : '';
?>
<section class="section-lg">
    <div class="container">
        <div class="grid items-start gap-8 lg:grid-cols-2 lg:gap-16">
            <div class="reveal">
                <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?><p class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <h2 class="text-display-md mt-3"><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ((string) ($section['body'] ?? '') !== ''): ?><div class="mt-6 whitespace-pre-line text-body-md text-charcoal/72"><?= htmlspecialchars((string) ($section['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="reveal reveal-delay-2 rounded-3xl border border-charcoal/6 bg-ivory-warm px-8 py-8 md:px-10 md:py-10" style="background: var(--color-ivory-warm);">
                <?php if ($sideTitle !== ''): ?><p class="section-label"><?= htmlspecialchars($sideTitle, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <?php if ($sideBody !== ''): ?><div class="mt-5 whitespace-pre-line text-body-md text-charcoal/72"><?= htmlspecialchars($sideBody, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</section>
