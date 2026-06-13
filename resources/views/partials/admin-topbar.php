<header class="border-b border-slate-200 bg-white">
    <div class="flex items-center justify-between px-5 py-4 lg:px-8">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Workspace</p>
            <h1 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars((string) ($title ?? 'Admin Dashboard'), ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700 focus-visible:outline-slate-900">Search</button>
            <button type="button" class="rounded bg-slate-900 px-3 py-2 text-sm text-white focus-visible:outline-slate-900">Quick Add</button>
            <?php if (app_auth()->check()): ?>
                <span class="hidden text-sm text-slate-600 lg:inline"><?= htmlspecialchars((string) ((current_user()['email'] ?? 'admin')), ENT_QUOTES, 'UTF-8') ?></span>
                <form method="post" action="<?= htmlspecialchars(admin_url('logout'), ENT_QUOTES, 'UTF-8') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700 focus-visible:outline-slate-900">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</header>
