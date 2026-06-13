<!doctype html>
<html lang="en" class="h-full bg-[#f6f1ea]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found | Mesh Photography</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_top_left,rgba(166,127,99,0.18),transparent_40%),linear-gradient(180deg,#f6f1ea_0%,#efe7dc_100%)] font-body text-ink antialiased">
    <main class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-16 lg:px-10">
        <div class="grid w-full gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
            <section>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-clay">404 / Not Found</p>
                <h1 class="mt-5 max-w-3xl font-display text-5xl leading-none text-ink sm:text-6xl lg:text-7xl">This page slipped out of the frame.</h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-stone-700 sm:text-lg">The content you requested is unavailable, unpublished, or no longer lives at this address. The rest of the site is intact, and you can continue from the links below.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-full bg-ink px-6 py-3 text-sm font-semibold text-white transition hover:bg-black focus:outline-none focus:ring-2 focus:ring-clay focus:ring-offset-2 focus:ring-offset-sand">Return Home</a>
                    <a href="<?= htmlspecialchars(base_url('portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold text-ink transition hover:border-clay hover:text-clay focus:outline-none focus:ring-2 focus:ring-clay focus:ring-offset-2 focus:ring-offset-sand">Browse Portfolio</a>
                </div>
                <div class="mt-10 flex flex-wrap gap-6 text-sm text-stone-600">
                    <span>Request ID: <?= htmlspecialchars((string) ($requestId ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="<?= htmlspecialchars(base_url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="font-medium text-pine underline decoration-transparent underline-offset-4 transition hover:decoration-current">Contact the studio</a>
                </div>
            </section>

            <aside class="rounded-[2rem] border border-white/70 bg-white/70 p-8 shadow-[0_30px_80px_-40px_rgba(23,20,17,0.35)] backdrop-blur">
                <div class="rounded-[1.5rem] border border-stone-200/80 bg-[#fbf8f3] p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Suggested next steps</p>
                    <ul class="mt-6 space-y-4 text-sm leading-7 text-stone-700">
                        <li>Visit the journal for recent planning notes and case studies.</li>
                        <li>Open the services page if you were looking for collections or pricing direction.</li>
                        <li>Use the contact form if you followed an outdated link from a previous campaign or proofing email.</li>
                    </ul>
                    <?php if (($debug ?? false) && isset($exception) && $exception instanceof Throwable): ?>
                        <div class="mt-8 rounded-2xl bg-stone-950 px-5 py-4 text-xs text-stone-100">
                            <p class="font-semibold uppercase tracking-[0.2em] text-stone-300">Debug details</p>
                            <p class="mt-3 break-words"><?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mt-2 break-all text-stone-400"><?= htmlspecialchars($exception->getFile() . ':' . $exception->getLine(), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
