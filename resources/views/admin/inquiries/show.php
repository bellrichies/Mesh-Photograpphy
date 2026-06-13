<?php
$inquiry = isset($inquiry) && is_array($inquiry) ? $inquiry : [];
$notes = isset($notes) && is_array($notes) ? $notes : [];
$oldInput = isset($oldInput) && is_array($oldInput) ? $oldInput : [];
$adminPath = (string) ($adminPath ?? '/admin');
$inquiryId = (int) ($inquiry['id'] ?? 0);
?>

<section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Inquiry Details</h2>
                <p class="mt-1 text-sm text-slate-600">Review the original message, update the workflow status, and attach internal notes.</p>
            </div>
            <a href="<?= htmlspecialchars($adminPath . '/inquiries', ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700">Back to Inquiries</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars(trim((string) (($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars((string) ($inquiry['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ((string) ($inquiry['phone'] ?? '') !== ''): ?><p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars((string) ($inquiry['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        </div>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium <?= match ((string) ($inquiry['status'] ?? 'new')) { 'responded' => 'bg-emerald-100 text-emerald-700', 'archived' => 'bg-slate-200 text-slate-700', 'spam' => 'bg-rose-100 text-rose-700', 'in_progress' => 'bg-amber-100 text-amber-700', default => 'bg-sky-100 text-sky-700' } ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($inquiry['status'] ?? 'new'))), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2 text-sm text-slate-700">
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Service Interest</dt><dd class="mt-1"><?= htmlspecialchars((string) ($inquiry['service_interest'] ?? 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Preferred Date</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['preferred_date'] ?? '') !== '' ? ($inquiry['preferred_date'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Budget Range</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['budget_range'] ?? '') !== '' ? ($inquiry['budget_range'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Location</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['location'] ?? '') !== '' ? ($inquiry['location'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Referral Source</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['referral_source'] ?? '') !== '' ? ($inquiry['referral_source'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Received</dt><dd class="mt-1"><?= htmlspecialchars((string) ($inquiry['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Company</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['company_name'] ?? '') !== '' ? ($inquiry['company_name'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.14em] text-slate-500">Source IP</dt><dd class="mt-1"><?= htmlspecialchars((string) (($inquiry['source_ip'] ?? '') !== '' ? ($inquiry['source_ip'] ?? '') : 'Not captured'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    </dl>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Message</p>
                    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700"><?= htmlspecialchars((string) ($inquiry['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Status</p>
                    <form method="post" action="<?= htmlspecialchars($adminPath . '/inquiries/status/' . $inquiryId, ENT_QUOTES, 'UTF-8') ?>" class="mt-4 space-y-4">
                        <?= csrf_field() ?>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'archived' => 'Archived', 'spam' => 'Spam'] as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($inquiry['status'] ?? 'new') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Update Status</button>
                    </form>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Internal Notes</p>
                    <form method="post" action="<?= htmlspecialchars($adminPath . '/inquiries/notes/' . $inquiryId, ENT_QUOTES, 'UTF-8') ?>" class="mt-4 space-y-4">
                        <?= csrf_field() ?>
                        <textarea name="note" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Add an internal follow-up note."><?= htmlspecialchars((string) ($oldInput['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-medium text-white">Add Note</button>
                    </form>

                    <div class="mt-5 space-y-4">
                        <?php if ($notes === []): ?>
                            <p class="text-sm text-slate-500">No notes recorded yet.</p>
                        <?php endif; ?>
                        <?php foreach ($notes as $note): ?>
                            <article class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 text-xs uppercase tracking-[0.14em] text-slate-500">
                                    <span><?= htmlspecialchars(trim((string) (($note['first_name'] ?? '') . ' ' . ($note['last_name'] ?? ''))) !== '' ? trim((string) (($note['first_name'] ?? '') . ' ' . ($note['last_name'] ?? ''))) : 'System', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span><?= htmlspecialchars((string) ($note['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700"><?= htmlspecialchars((string) ($note['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>