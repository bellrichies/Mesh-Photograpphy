<?php $metrics = isset($metrics) && is_array($metrics) ? $metrics : []; ?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <h2 class="text-2xl font-semibold text-slate-900">Dashboard Overview</h2>
        <p class="mt-2 text-slate-600">Live KPI cards below are pulled from the current database records across content, media, and inbound pipeline entities.</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Pages</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['pages_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['pages_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Galleries</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['galleries_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['galleries_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Blog Posts</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['blog_posts_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['blog_posts_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Media Assets</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['media_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500">Active library items</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Services</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['services_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['services_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Testimonials</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['testimonials_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['testimonials_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Hero Slides</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['hero_slides_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['hero_slides_published'] ?? 0) ?> published</p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Bookings</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($metrics['bookings_total'] ?? 0) ?></p>
                <p class="mt-1 text-sm text-slate-500"><?= (int) ($metrics['bookings_new'] ?? 0) ?> new, <?= (int) ($metrics['bookings_quoted'] ?? 0) ?> quoted</p>
            </article>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900">Content Health</h3>
            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Published pages</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['pages_published'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Published galleries</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['galleries_published'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Published blog posts</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['blog_posts_published'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Published services</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['services_published'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Published hero slides</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['hero_slides_published'] ?? 0) ?></span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900">Inbound Pipeline</h3>
            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Total inquiries</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['inquiries_total'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">New inquiries</span>
                    <span class="text-lg font-semibold text-amber-700"><?= (int) ($metrics['inquiries_new'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Inquiries in progress</span>
                    <span class="text-lg font-semibold text-sky-700"><?= (int) ($metrics['inquiries_in_progress'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Total bookings</span>
                    <span class="text-lg font-semibold text-slate-900"><?= (int) ($metrics['bookings_total'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Quoted bookings</span>
                    <span class="text-lg font-semibold text-emerald-700"><?= (int) ($metrics['bookings_quoted'] ?? 0) ?></span>
                </div>
            </div>
        </section>
    </div>
</section>
