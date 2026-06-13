<?php
$item = isset($item) && is_array($item) ? $item : [];
$variant = (string) ($variant ?? 'default');
$portraitUrl = (string) ($item['portrait_url'] ?? '');

if ($portraitUrl === '') {
    $portraitUrl = app_media_url(
        (string) ($item['portrait_directory'] ?? ''),
        (string) ($item['portrait_stored_name'] ?? '')
    );
}
?>

<?php if ($variant === 'detailed'): ?>
    <!-- Detailed Testimonial Card -->
    <article class="card overflow-hidden">
        <div class="grid gap-0 lg:grid-cols-[280px,1fr]">
            <!-- Portrait Side -->
            <div class="bg-charcoal relative min-h-[280px] overflow-hidden">
                <?php if ($portraitUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($portraitUrl, ENT_QUOTES, 'UTF-8') ?>" 
                         alt="<?= htmlspecialchars((string) (($item['portrait_alt_text'] ?? '') !== '' ? ($item['portrait_alt_text'] ?? '') : ($item['client_name'] ?? 'Client portrait')), ENT_QUOTES, 'UTF-8') ?>" 
                         class="h-full w-full object-cover" 
                         loading="lazy">
                <?php else: ?>
                    <div class="absolute inset-0" style="background: linear-gradient(160deg, #1a1a1a 0%, #2e2a26 52%, #6d4f35 100%);"></div>
                <?php endif; ?>
            </div>
            
            <!-- Content Side -->
            <div class="p-7 lg:p-10">
                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-3 text-caption" style="color: var(--color-bronze);">
                    <?php if ((int) ($item['rating'] ?? 0) > 0): ?>
                        <span class="flex items-center gap-0.5">
                            <?php for ($i = 0; $i < (int) ($item['rating'] ?? 0); $i++): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            <?php endfor; ?>
                        </span>
                    <?php endif; ?>
                    <?php if ((string) ($item['location'] ?? '') !== ''): ?>
                        <span><?= htmlspecialchars((string) ($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ((string) ($item['event_date'] ?? '') !== ''): ?>
                        <span><?= htmlspecialchars((string) ($item['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Quote -->
                <blockquote class="mt-6 text-display-sm text-charcoal">&ldquo;<?= htmlspecialchars((string) ($item['quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?>&rdquo;</blockquote>
                
                <!-- Client Info -->
                <p class="mt-6 text-caption text-taupe">
                    <?= htmlspecialchars((string) ($item['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php if ((string) ($item['client_label'] ?? '') !== ''): ?>
                        <span class="text-slate">, <?= htmlspecialchars((string) ($item['client_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </p>
                
                <!-- Long Form Story -->
                <?php if ((string) ($item['long_form_story'] ?? '') !== ''): ?>
                    <p class="mt-5 text-body-sm text-slate leading-relaxed"><?= nl2br(htmlspecialchars((string) ($item['long_form_story'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                
                <!-- Tags -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <?php if ((string) ($item['service_title'] ?? '') !== ''): ?>
                        <a href="<?= htmlspecialchars(base_url('services/' . rawurlencode((string) ($item['service_slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" 
                           class="inline-flex rounded-full bg-cream px-3 py-1.5 text-caption text-taupe hover:bg-bronze hover:text-white transition-colors duration-300">
                            <?= htmlspecialchars((string) ($item['service_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endif; ?>
                    <?php if ((string) ($item['gallery_title'] ?? '') !== ''): ?>
                        <a href="<?= htmlspecialchars(base_url('portfolio/' . rawurlencode((string) ($item['gallery_slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" 
                           class="inline-flex rounded-full bg-cream px-3 py-1.5 text-caption text-taupe hover:bg-bronze hover:text-white transition-colors duration-300">
                            <?= htmlspecialchars((string) ($item['gallery_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
    
<?php else: ?>
    <!-- Compact Testimonial Card (for dark backgrounds) -->
    <blockquote class="rounded-2xl p-7 border border-white/10 h-full flex flex-col" style="background: rgba(255,255,255,0.06); backdrop-filter: blur(8px);">
        <!-- Star Rating -->
        <?php if ((int) ($item['rating'] ?? 0) > 0): ?>
            <div class="flex gap-1 mb-5">
                <?php for ($i = 0; $i < (int) ($item['rating'] ?? 0); $i++): ?>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" style="color: var(--color-gold);">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        
        <!-- Quote -->
        <p class="text-body-md leading-relaxed text-white/85 flex-1">&ldquo;<?= htmlspecialchars((string) ($item['quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?>&rdquo;</p>
        
        <!-- Client Info -->
        <footer class="mt-6 flex items-center gap-3 pt-5 border-t border-white/8">
            <?php if ($portraitUrl !== ''): ?>
                <img src="<?= htmlspecialchars($portraitUrl, ENT_QUOTES, 'UTF-8') ?>" 
                     alt="<?= htmlspecialchars((string) (($item['portrait_alt_text'] ?? '') !== '' ? ($item['portrait_alt_text'] ?? '') : ($item['client_name'] ?? 'Client portrait')), ENT_QUOTES, 'UTF-8') ?>" 
                     class="h-11 w-11 rounded-full object-cover ring-2 ring-white/10" 
                     loading="lazy">
            <?php endif; ?>
            <div>
                <p class="text-caption text-white"><?= htmlspecialchars((string) ($item['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ((string) ($item['client_label'] ?? '') !== ''): ?>
                    <p class="text-xs text-white/50 mt-0.5"><?= htmlspecialchars((string) ($item['client_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        </footer>
    </blockquote>
<?php endif; ?>
