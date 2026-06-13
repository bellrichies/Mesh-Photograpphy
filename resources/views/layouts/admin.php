<!doctype html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') : 'Admin' ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script src="<?= htmlspecialchars(asset_url('js/vendor-jquery-3.7.1.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('js/admin-core.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <style>
        :focus-visible {
            outline: 3px solid rgba(15, 23, 42, 0.38);
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
<body class="h-full font-body text-slate-800">
    <a href="#admin-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-slate-950 focus:px-4 focus:py-2 focus:text-sm focus:text-white">Skip to admin content</a>
    <div class="flex min-h-full">
        <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

        <div class="flex min-h-screen flex-1 flex-col">
            <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>
            <?php include __DIR__ . '/../partials/breadcrumbs.php'; ?>
            <?php include __DIR__ . '/../partials/flash-messages.php'; ?>

            <main id="admin-main-content" tabindex="-1" class="flex-1 px-5 pb-10 pt-6 lg:px-8">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <div id="admin-toast-root" class="pointer-events-none fixed right-4 top-4 z-[90] flex w-full max-w-sm flex-col gap-3"></div>

    <script src="<?= htmlspecialchars(asset_url('js/app.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
