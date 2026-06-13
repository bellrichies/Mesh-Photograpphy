<?php
$session = app_session();
$success = $session->getFlash('success');
$error = $session->getFlash('error');
?>
<?php if ($success || $error): ?>
    <section class="mx-auto mt-4 w-full max-w-7xl px-5 lg:px-8" data-flash-container aria-live="polite" aria-atomic="true">
        <?php if ($success): ?>
            <div class="mb-3 flex items-start justify-between rounded-[1.4rem] border border-emerald-200/80 bg-white px-5 py-4 text-emerald-900 shadow-[0_16px_40px_-28px_rgba(16,185,129,0.45)]" data-flash data-flash-autohide="true" role="status">
                <div class="pr-4">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-emerald-600">Success</p>
                    <p class="mt-1 text-sm leading-7"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <button type="button" class="ml-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-emerald-200 text-sm" aria-label="Dismiss success message" data-flash-close>×</button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-3 flex items-start justify-between rounded-[1.4rem] border border-rose-200/80 bg-white px-5 py-4 text-rose-900 shadow-[0_16px_40px_-28px_rgba(244,63,94,0.42)]" data-flash role="alert" aria-live="assertive">
                <div class="pr-4">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-rose-600">Attention</p>
                    <p class="mt-1 text-sm leading-7"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <button type="button" class="ml-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 text-sm" aria-label="Dismiss error message" data-flash-close>×</button>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
