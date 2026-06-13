<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'service_id' => ''];
$services = isset($services) && is_array($services) ? $services : [];
$adminPath = (string) ($adminPath ?? '/admin');
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Booking Requests</h2>
                <p class="mt-1 text-sm text-slate-600">Track availability requests, date priorities, and lightweight booking workflow progress.</p>
            </div>
        </div>

        <form method="get" action="<?= htmlspecialchars($adminPath . '/bookings', ENT_QUOTES, 'UTF-8') ?>" class="mb-5 grid gap-3 lg:grid-cols-4">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Search name, email, event, location">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'quoted' => 'Quoted', 'confirmed' => 'Confirmed', 'archived' => 'Archived'] as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="service_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All services</option>
                <?php foreach ($services as $service): ?>
                    <?php $serviceId = (int) ($service['id'] ?? 0); ?>
                    <option value="<?= $serviceId ?>" <?= (string) ($filters['service_id'] ?? '') === (string) $serviceId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply Filters</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium">Request</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Linked Inquiry</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if ($items === []): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No booking requests matched the current filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <?php $bookingRequestId = (int) ($item['id'] ?? 0); ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars(trim((string) (($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <p><?= htmlspecialchars((string) ($item['event_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['requested_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?php if ((string) ($item['requested_time'] ?? '') !== ''): ?> at <?= htmlspecialchars(substr((string) ($item['requested_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                                <?php if ((string) ($item['service_title'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500">Service: <?= htmlspecialchars((string) ($item['service_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            </td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= match ((string) ($item['status'] ?? 'new')) { 'confirmed' => 'bg-emerald-100 text-emerald-700', 'archived' => 'bg-slate-200 text-slate-700', 'quoted' => 'bg-violet-100 text-violet-700', 'in_progress' => 'bg-amber-100 text-amber-700', default => 'bg-sky-100 text-sky-700' } ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($item['status'] ?? 'new'))), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="px-4 py-4 text-slate-600"><?= (int) ($item['inquiry_record_id'] ?? 0) > 0 ? ('#' . (int) ($item['inquiry_record_id'] ?? 0)) : 'None' ?></td>
                            <td class="px-4 py-4"><a href="<?= htmlspecialchars($adminPath . '/bookings/view/' . $bookingRequestId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>