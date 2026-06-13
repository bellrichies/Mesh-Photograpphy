<?php $section = isset($section) && is_array($section) ? $section : []; ?>
<section class="section-lg" style="background: var(--color-ivory-warm);">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? 'Story'), ENT_QUOTES, 'UTF-8') ?></p>
            <h2 class="text-display-lg mt-4"><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ((string) ($section['body'] ?? '') !== ''): ?>
                <div class="mt-6 whitespace-pre-line text-body-lg text-charcoal/68"><?= htmlspecialchars((string) ($section['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <!-- Decorative line -->
            <div class="mx-auto mt-8 h-px w-16" style="background: linear-gradient(90deg, transparent, var(--color-bronze), transparent);"></div>
        </div>
    </div>
</section>