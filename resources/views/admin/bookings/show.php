<?php
$bookingRequest = isset($bookingRequest) && is_array($bookingRequest) ? $bookingRequest : [];
$adminPath = (string) ($adminPath ?? '/admin');
$bookingRequestId = (int) ($bookingRequest['id'] ?? 0);
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Booking Request Details</h2>
                <p class="mt-1 text-sm text-slate-600">Review availability details and update the booking workflow status.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/bookings', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Bookings</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars(trim((string) (($bookingRequest['first_name'] ?? '') . ' ' . ($bookingRequest['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars((string) ($bookingRequest['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ((string) ($bookingRequest['phone'] ?? '') !== ''): ?><p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars((string) ($bookingRequest['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        </div>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= match ((string) ($bookingRequest['status'] ?? 'new')) { 'confirmed' => 'bg-emerald-100 text-emerald-700', 'archived' => 'bg-slate-200 text-slate-700', 'quoted' => 'bg-violet-100 text-violet-700', 'in_progress' => 'bg-amber-100 text-amber-700', default => 'bg-sky-100 text-sky-700' } ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($bookingRequest['status'] ?? 'new'))), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2 text-sm text-slate-700">
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Requested Date</dt><dd class="mt-1"><?= htmlspecialchars((string) ($bookingRequest['requested_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Requested Time</dt><dd class="mt-1"><?= htmlspecialchars((string) (($bookingRequest['requested_time'] ?? '') !== '' ? substr((string) ($bookingRequest['requested_time'] ?? ''), 0, 5) : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Event Type</dt><dd class="mt-1"><?= htmlspecialchars((string) ($bookingRequest['event_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Location</dt><dd class="mt-1"><?= htmlspecialchars((string) ($bookingRequest['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Hours Needed</dt><dd class="mt-1"><?= htmlspecialchars((string) (($bookingRequest['hours_needed'] ?? '') !== '' ? ($bookingRequest['hours_needed'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Guest Count</dt><dd class="mt-1"><?= htmlspecialchars((string) (($bookingRequest['guest_count'] ?? '') !== '' ? ($bookingRequest['guest_count'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Service</dt><dd class="mt-1"><?= htmlspecialchars((string) (($bookingRequest['service_title'] ?? '') !== '' ? ($bookingRequest['service_title'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Received</dt><dd class="mt-1"><?= htmlspecialchars((string) ($bookingRequest['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    </dl>
                </section>

                <?php if ((string) ($bookingRequest['notes'] ?? '') !== ''): ?>
                    <section class="rounded-xl border border-slate-200 bg-white p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Notes</p>
                        <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700"><?= htmlspecialchars((string) ($bookingRequest['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    </section>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Status</p>
                    <form method="post" action="<?= htmlspecialchars($adminPath . '/bookings/status/' . $bookingRequestId, ENT_QUOTES, 'UTF-8') ?>" class="mt-4 space-y-4">
                        <?= csrf_field() ?>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'quoted' => 'Quoted', 'confirmed' => 'Confirmed', 'archived' => 'Archived'] as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($bookingRequest['status'] ?? 'new') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Update Status</button>
                    </form>
                </section>

                <?php if ((int) ($bookingRequest['inquiry_record_id'] ?? 0) > 0): ?>
                    <section class="rounded-xl border border-slate-200 bg-white p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Linked Inquiry</p>
                        <p class="mt-3 text-sm text-slate-700">Connected to inquiry #<?= (int) ($bookingRequest['inquiry_record_id'] ?? 0) ?>.</p>
                        <a href="<?= htmlspecialchars($adminPath . '/inquiries/view/' . (int) ($bookingRequest['inquiry_record_id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex rounded border border-slate-300 px-3 py-2 text-sm text-slate-700">Open Inquiry</a>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>