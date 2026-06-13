<!doctype html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Error | Mesh Photography</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_top_left,rgba(148,163,184,0.22),transparent_32%),linear-gradient(180deg,#f8fafc_0%,#e2e8f0_100%)] font-body text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16">
        <div class="w-full rounded-[2rem] border border-white/70 bg-white/80 p-8 shadow-[0_28px_70px_-36px_rgba(15,23,42,0.4)] backdrop-blur lg:p-10">
            <div class="flex flex-wrap items-start justify-between gap-6 border-b border-slate-200 pb-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">500 / Admin interruption</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">The admin workspace hit an unexpected error.</h1>
                </div>
                <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Request ID <?= htmlspecialchars((string) ($requestId ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <section>
                    <p class="text-sm leading-7 text-slate-700"><?= htmlspecialchars((string) ($safeMessage ?? 'Something went wrong on our side. Please try again shortly.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="<?= htmlspecialchars(base_url(trim((string) config('app.admin_path', '/admin'), '/') . '/dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-black">Return to dashboard</a>
                        <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-slate-500">Open public site</a>
                    </div>
                </section>

                <aside class="rounded-[1.5rem] bg-slate-950 p-6 text-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Operational note</p>
                    <ul class="mt-4 space-y-3 text-sm leading-7 text-slate-300">
                        <li>This error has been written to storage logs for follow-up.</li>
                        <li>Retry the previous action after refreshing the page if the issue was transient.</li>
                        <li>Use the request ID above when tracing the failure in the log file.</li>
                    </ul>
                </aside>
            </div>

            <?php if (($debug ?? false) && isset($exception) && $exception instanceof Throwable): ?>
                <section class="mt-8 rounded-[1.5rem] border border-amber-200 bg-amber-50 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-700">Debug details</p>
                    <p class="mt-3 text-sm font-medium text-amber-950"><?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-2 break-all text-xs text-amber-800"><?= htmlspecialchars($exception->getFile() . ':' . $exception->getLine(), ENT_QUOTES, 'UTF-8') ?></p>
                </section>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
