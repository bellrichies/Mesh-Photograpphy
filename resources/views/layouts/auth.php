<!doctype html>
<html lang="en" class="h-full bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') : 'Authentication' ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :focus-visible {
            outline: 3px solid rgba(176, 137, 104, 0.9);
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            html:focus-within {
                scroll-behavior: auto;
            }

            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="h-full font-body text-zinc-100">
    <a href="#auth-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-zinc-950">Skip to sign in</a>
    <div class="relative flex min-h-full items-center justify-center overflow-hidden px-5 py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(176,137,104,0.22),transparent_45%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.08),transparent_38%)]"></div>
        <main id="auth-content" tabindex="-1" class="relative z-10 w-full max-w-md rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur">
            <?php include __DIR__ . '/../partials/flash-messages.php'; ?>
            <?= $content ?? '' ?>
        </main>
    </div>

    <script src="<?= htmlspecialchars(asset_url('js/app.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
