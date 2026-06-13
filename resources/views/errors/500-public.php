<!doctype html>
<html lang="en" class="h-full bg-[#f4efe8]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temporary Studio Interruption | Mesh Photography</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_15%_15%,rgba(143,111,82,0.2),transparent_36%),radial-gradient(circle_at_85%_25%,rgba(63,90,79,0.16),transparent_32%),linear-gradient(180deg,#f4efe8_0%,#ece4d8_100%)] font-body text-espresso antialiased">
    <main class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-16 lg:px-10">
        <div class="grid w-full gap-10 rounded-[2.25rem] border border-white/60 bg-white/50 p-8 shadow-[0_32px_80px_-42px_rgba(27,23,20,0.38)] backdrop-blur lg:grid-cols-[1.2fr_0.8fr] lg:p-12">
            <section>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-bronze">500 / Service Interruption</p>
                <h1 class="mt-5 max-w-3xl font-display text-5xl leading-none sm:text-6xl">A temporary interruption occurred while loading the experience.</h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-stone-700 sm:text-lg"><?= htmlspecialchars((string) ($safeMessage ?? 'Something went wrong on our side. Please try again shortly.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-full bg-espresso px-6 py-3 text-sm font-semibold text-white transition hover:bg-black focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2 focus:ring-offset-parchment">Go to homepage</a>
                    <a href="<?= htmlspecialchars(base_url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold text-espresso transition hover:border-bronze hover:text-bronze focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2 focus:ring-offset-parchment">Send an inquiry instead</a>
                </div>
                <p class="mt-8 text-sm text-stone-600">If this persists, include request ID <span class="font-semibold text-espresso"><?= htmlspecialchars((string) ($requestId ?? ''), ENT_QUOTES, 'UTF-8') ?></span> when you contact the studio.</p>
            </section>

            <aside class="rounded-[1.75rem] bg-[#fbf8f3] p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">While you wait</p>
                <ul class="mt-5 space-y-4 text-sm leading-7 text-stone-700">
                    <li>Review recent portfolio stories for a quick overview of the studio’s visual direction.</li>
                    <li>Browse the journal for planning guidance and recent editorial notes.</li>
                    <li>Retry the page in a moment if you were accessing a gallery, blog post, or inquiry form.</li>
                </ul>
                <?php if (($debug ?? false) && isset($exception) && $exception instanceof Throwable): ?>
                    <div class="mt-8 rounded-2xl bg-stone-950 px-5 py-4 text-xs text-stone-100">
                        <p class="font-semibold uppercase tracking-[0.2em] text-stone-300">Debug details</p>
                        <p class="mt-3 break-words"><?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mt-2 break-all text-stone-400"><?= htmlspecialchars($exception->getFile() . ':' . $exception->getLine(), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>
</body>
</html>
