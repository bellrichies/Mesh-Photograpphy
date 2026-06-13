<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $seo = isset($seo) && is_array($seo) ? $seo : [];
    $brandPrimary = trim((string) app_setting('branding', 'brand_primary_color', '#9A7B5C'));
    $brandSecondary = trim((string) app_setting('branding', 'brand_secondary_color', '#1A1A1A'));
    $defaultKeywords = app_setting('seo', 'default_meta_keywords', []);
    $keywordContent = is_array($defaultKeywords) ? implode(', ', array_values(array_filter(array_map('strval', $defaultKeywords), static fn (string $value): bool => trim($value) !== ''))) : '';
    ?>
    <title><?= htmlspecialchars((string) ($seo['meta_title'] ?? ($title ?? config('app.name', 'Mesh Photograph'))), ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars((string) ($seo['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($keywordContent !== ''): ?>
        <meta name="keywords" content="<?= htmlspecialchars($keywordContent, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= htmlspecialchars((string) ($seo['canonical_url'] ?? base_url()), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars((string) ($seo['robots_content'] ?? 'index,follow'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="<?= htmlspecialchars($brandPrimary, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars((string) ($seo['site_name'] ?? config('app.name', 'Mesh Photograph')), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="<?= htmlspecialchars((string) ($seo['og_type'] ?? 'website'), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars((string) ($seo['og_title'] ?? ($seo['meta_title'] ?? ($title ?? config('app.name', 'Mesh Photograph')))), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars((string) ($seo['og_description'] ?? ($seo['meta_description'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars((string) ($seo['canonical_url'] ?? base_url()), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ((string) ($seo['og_image'] ?? '') !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars((string) ($seo['og_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:card" content="summary_large_image">
    <?php else: ?>
        <meta name="twitter:card" content="summary">
    <?php endif; ?>
<?php
$pageTitle = $seo['og_title'] ?? $seo['meta_title'] ?? $title ?? config('app.name', 'Mesh Photograph');
$metaDesc = $seo['og_description'] ?? $seo['meta_description'] ?? '';
?>
    <meta name="twitter:title" content="<?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars((string) $metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ((string) ($seo['og_image'] ?? '') !== ''): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars((string) ($seo['og_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    
    <style>
        :root {
            --color-brand-primary: <?= htmlspecialchars($brandPrimary, ENT_QUOTES, 'UTF-8') ?>;
            --color-brand-secondary: <?= htmlspecialchars($brandSecondary, ENT_QUOTES, 'UTF-8') ?>;
        }

        :focus-visible {
            outline: 2px solid rgba(154, 123, 92, 0.75);
            outline-offset: 2px;
        }

        html {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f5f1eb;
        }

        ::-webkit-scrollbar-thumb {
            background: #c4c0b8;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9a7b5c;
        }

        ::selection {
            background-color: rgba(154, 123, 92, 0.25);
            color: #1a1a1a;
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
<body class="h-full font-body text-charcoal antialiased bg-ivory">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-charcoal focus:px-4 focus:py-2 focus:text-sm focus:text-ivory">Skip to content</a>
    
    <!-- Header -->
    <?php include __DIR__ . '/../partials/public-header.php'; ?>
    
    <!-- Flash Messages -->
    <?php include __DIR__ . '/../partials/flash-messages.php'; ?>

    <!-- Main Content -->
    <main id="main-content" tabindex="-1" class="w-full">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../partials/public-footer.php'; ?>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="<?= htmlspecialchars(asset_url('js/app.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
