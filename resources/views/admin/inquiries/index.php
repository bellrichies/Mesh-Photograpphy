<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'service_interest' => ''];
$services = isset($services) && is_array($services) ? $services : [];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Inquiries</h2>
                <p class="mt-1 text-sm text-slate-600">Review incoming inquiries, track status, and keep internal notes tied to the contact workflow.</p>
            </div>
        </div>

        <form id="inquiry-filter-form" method="get" action="<?= htmlspecialchars($adminPath . '/inquiries', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 lg:grid-cols-4">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search name, email, location, message">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'archived' => 'Archived', 'spam' => 'Spam'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="service_interest" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All services</option>
                <?php foreach ($services as $service): ?>
                    <?php $serviceTitle = (string) ($service['title'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($serviceTitle, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['service_interest'] ?? '') === $serviceTitle ? 'selected' : '' ?>><?= htmlspecialchars($serviceTitle, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="page" value="<?= (int) ($pagination['page'] ?? 1) ?>">
            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div id="inquiry-results">
            <?php include __DIR__ . '/results.php'; ?>
        </div>
    </div>
</section>

<script>
    window.INQUIRY_ADMIN_CONFIG = {
        adminPath: <?= json_encode($adminPath) ?>,
        csrfKey: <?= json_encode((string) config('app.csrf_token_name', '_token')) ?>,
        csrfToken: <?= json_encode(app_csrf()->token()) ?>
    };
</script>
<script src="<?= htmlspecialchars(asset_url('js/admin-inquiries.js'), ENT_QUOTES, 'UTF-8') ?>"></script>