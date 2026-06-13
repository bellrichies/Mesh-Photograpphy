<?php
$adminGroups = [
    [
        'label' => 'Overview',
        'items' => [
            ['href' => '/admin/dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ],
    ],
    [
        'label' => 'Content',
        'items' => [
            ['href' => '/admin/pages', 'label' => 'Pages', 'icon' => 'pages'],
            ['href' => '/admin/blocks', 'label' => 'Reusable Blocks', 'icon' => 'blocks'],
            ['href' => '/admin/media', 'label' => 'Media Library', 'icon' => 'media'],
            ['href' => '/admin/galleries', 'label' => 'Portfolio', 'icon' => 'gallery'],
            ['href' => '/admin/gallery-categories', 'label' => 'Gallery Categories', 'icon' => 'folder'],
            ['href' => '/admin/services', 'label' => 'Services', 'icon' => 'services'],
            ['href' => '/admin/testimonials', 'label' => 'Testimonials', 'icon' => 'star'],
            ['href' => '/admin/hero-slides', 'label' => 'Hero Slider', 'icon' => 'carousel'],
        ],
    ],
    [
        'label' => 'Editorial',
        'items' => [
            ['href' => '/admin/blog/posts', 'label' => 'Blog Posts', 'icon' => 'blog'],
            ['href' => '/admin/blog/categories', 'label' => 'Blog Categories', 'icon' => 'category'],
            ['href' => '/admin/blog/tags', 'label' => 'Blog Tags', 'icon' => 'tag'],
        ],
    ],
    [
        'label' => 'Communication',
        'items' => [
            ['href' => '/admin/inquiries', 'label' => 'Inquiries', 'icon' => 'inbox'],
            ['href' => '/admin/bookings', 'label' => 'Bookings', 'icon' => 'calendar'],
        ],
    ],
    [
        'label' => 'System',
        'items' => [
            ['href' => '/admin/settings', 'label' => 'Settings', 'icon' => 'settings'],
            ['href' => '/admin/users', 'label' => 'Users', 'icon' => 'users'],
        ],
    ],
];

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$pathMatches = static function (string $needle) use ($currentPath): bool {
    $resolved = parse_url((string) app_href($needle), PHP_URL_PATH) ?? $needle;

    return $currentPath === $resolved
        || str_starts_with($currentPath, rtrim($resolved, '/') . '/');
};

$isActive = static function (string $path) use ($pathMatches): bool {
    if ($path === '/admin/dashboard') {
        return $pathMatches('/admin') || $pathMatches('/admin/dashboard');
    }

    return $pathMatches($path);
};

$itemClass = static function (string $path) use ($isActive): string {
    $base = 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 focus-visible:outline-white';

    if ($isActive($path)) {
        return $base . ' bg-white/14 text-white shadow-sm';
    }

    return $base . ' text-slate-200 hover:bg-white/10 hover:text-white';
};

$iconClass = static function (string $path) use ($isActive): string {
    return $isActive($path) ? 'text-white' : 'text-slate-400 group-hover:text-white';
};

$renderIcon = static function (string $icon, string $classes = ''): string {
    $classAttr = htmlspecialchars(trim('h-5 w-5 ' . $classes), ENT_QUOTES, 'UTF-8');

    return match ($icon) {
        'dashboard' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="8" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="15" width="7" height="6" rx="1.5"></rect></svg>',
        'pages' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3.5h8.5L20 8v12.5H7z"></path><path d="M15.5 3.5V8H20"></path><path d="M10 12h7"></path><path d="M10 16h7"></path><path d="M10 8.5h2.5"></path></svg>',
        'blocks' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="1.5"></rect><rect x="13" y="3" width="8" height="5" rx="1.5"></rect><rect x="13" y="10" width="8" height="11" rx="1.5"></rect><rect x="3" y="13" width="8" height="8" rx="1.5"></rect></svg>',
        'media' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="8.5" cy="9" r="1.5"></circle><path d="m21 15-4.5-4.5L7 20"></path></svg>',
        'gallery' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5z"></path><path d="m7 15 2.5-2.5L12 15l2-2 3 3"></path><circle cx="8.5" cy="8.5" r="1.25"></circle></svg>',
        'folder' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5v8A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5z"></path></svg>',
        'services' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 10V6a2 2 0 0 0-4 0v4"></path><path d="M5 10h14l-1 9a2 2 0 0 1-2 1.75H8A2 2 0 0 1 6 19z"></path></svg>',
        'star' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 2.8 5.67 6.2.9-4.5 4.38 1.06 6.18L12 17.2 6.44 20.13 7.5 13.95 3 9.57l6.2-.9z"></path></svg>',
        'carousel' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M7 5v14"></path><path d="M17 5v14"></path><path d="M10 12h4"></path></svg>',
        'blog' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4.5h10l4 4V19a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4 19V6a1.5 1.5 0 0 1 1-1.5z"></path><path d="M15 4.5V9h4"></path><path d="M8 12h8"></path><path d="M8 16h6"></path></svg>',
        'category' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H10l2 2h5.5A2.5 2.5 0 0 1 20 9.5v7A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5z"></path></svg>',
        'tag' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13.5 12.5 21 3 11.5V5h6.5z"></path><circle cx="8.25" cy="8.25" r="1.25"></circle></svg>',
        'inbox' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V18a2 2 0 0 1-2 2h-3.25a2 2 0 0 1-1.42-.59L12 18.08l-1.33 1.33a2 2 0 0 1-1.42.59H6a2 2 0 0 1-2-2z"></path><path d="M4 13h4l2 3h4l2-3h4"></path></svg>',
        'calendar' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 10h18"></path></svg>',
        'settings' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.83 2.83l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.92V20a2 2 0 1 1-4 0v-.17a1 1 0 0 0-.67-.95 1 1 0 0 0-1.1.24l-.11.1a2 2 0 1 1-2.82-2.83l.1-.1a1 1 0 0 0 .24-1.1 1 1 0 0 0-.95-.67H4a2 2 0 1 1 0-4h.17a1 1 0 0 0 .95-.67 1 1 0 0 0-.24-1.1l-.1-.11a2 2 0 1 1 2.83-2.82l.1.1a1 1 0 0 0 1.1.24H9a1 1 0 0 0 .67-.95V4a2 2 0 1 1 4 0v.17a1 1 0 0 0 .67.95 1 1 0 0 0 1.1-.24l.11-.1a2 2 0 1 1 2.82 2.83l-.1.1a1 1 0 0 0-.24 1.1V9c0 .42.26.8.67.95H20a2 2 0 1 1 0 4h-.17a1 1 0 0 0-.95.67z"></path></svg>',
        'users' => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="3"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 4.13a4 4 0 0 1 0 7.75"></path></svg>',
        default => '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"></circle></svg>',
    };
};
?>
<aside class="hidden w-72 shrink-0 border-r border-white/10 bg-slate-950 text-slate-100 lg:flex lg:flex-col">
    <div class="flex h-20 shrink-0 items-center border-b border-white/10 px-5">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-300/25 bg-amber-300/10 text-amber-100">
            <span class="font-semibold tracking-[0.12em]">M</span>
        </div>
        <div class="ml-3 min-w-0">
            <p class="font-semibold leading-tight text-white"><?= htmlspecialchars((string) config('app.name', 'Mesh Photography'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-0.5 text-[10px] uppercase tracking-[0.2em] text-slate-400">Admin Panel</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Admin navigation">
        <div class="space-y-6">
            <?php foreach ($adminGroups as $group): ?>
                <section>
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500"><?= htmlspecialchars((string) $group['label'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="space-y-0.5">
                        <?php foreach ($group['items'] as $item): ?>
                            <?php $path = (string) $item['href']; ?>
                            <a href="<?= htmlspecialchars(app_href($path), ENT_QUOTES, 'UTF-8') ?>" <?= $isActive($path) ? 'aria-current="page"' : '' ?> class="<?= $itemClass($path) ?>">
                                <?= $renderIcon((string) ($item['icon'] ?? ''), $iconClass($path)) ?>
                                <span><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </nav>
</aside>
