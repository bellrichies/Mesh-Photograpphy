<?php
$block = isset($block) && is_array($block) ? $block : [];
?>
<?php if ((string) ($block['title'] ?? '') !== ''): ?><p class="font-medium text-charcoal"><?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<?php if ((string) ($block['body'] ?? '') !== ''): ?><p class="mt-1 max-w-xl text-sm leading-7 text-charcoal/70"><?= htmlspecialchars((string) ($block['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>