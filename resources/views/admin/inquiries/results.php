<?php
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : ['q' => '', 'status' => '', 'service_interest' => ''];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$adminPath = (string) ($adminPath ?? '/admin');

$statusBadgeClass = static function (string $status): string {
    return match ($status) {
        'responded' => 'inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700',
        'archived' => 'inline-flex rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700',
        'spam' => 'inline-flex rounded-full bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700',
        'in_progress' => 'inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700',
        default => 'inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700',
    };
};
?>

<div class="overflow-x-auto rounded-xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-slate-600">
            <tr>
                <th class="px-4 py-3 font-medium">Contact</th>
                <th class="px-4 py-3 font-medium">Project</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Notes</th>
                <th class="px-4 py-3 font-medium">Received</th>
                <th class="px-4 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            <?php if ($items === []): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No inquiries matched the current filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <?php
                $inquiryId = (int) ($item['id'] ?? 0);
                $status = (string) ($item['status'] ?? 'new');
                ?>
                <tr data-inquiry-row data-inquiry-id="<?= $inquiryId ?>">
                    <td class="px-4 py-4 align-top">
                        <p class="font-medium text-slate-900"><?= htmlspecialchars(trim((string) (($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ((string) ($item['phone'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    </td>
                    <td class="px-4 py-4 text-slate-600">
                        <?php if ((string) ($item['service_interest'] ?? '') !== ''): ?><p><?= htmlspecialchars((string) ($item['service_interest'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        <?php if ((string) ($item['location'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars((string) ($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        <?php if ((string) ($item['preferred_date'] ?? '') !== ''): ?><p class="mt-1 text-xs text-slate-500">Preferred date: <?= htmlspecialchars((string) ($item['preferred_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    </td>
                    <td class="px-4 py-4 align-top">
                        <span data-inquiry-status-badge class="<?= htmlspecialchars($statusBadgeClass($status), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8') ?></span>
                        <select data-inquiry-status-select data-previous-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                            <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'archived' => 'Archived', 'spam' => 'Spam'] as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="px-4 py-4 text-slate-600"><?= (int) ($item['note_count'] ?? 0) ?></td>
                    <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars((string) ($item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-4"><a href="<?= htmlspecialchars($adminPath . '/inquiries/view/' . $inquiryId, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-5 flex items-center justify-between text-sm text-slate-600">
    <p>Page <?= (int) ($pagination['page'] ?? 1) ?> of <?= (int) ($pagination['total_pages'] ?? 1) ?> • Total <?= (int) ($pagination['total'] ?? 0) ?></p>
    <div class="flex gap-2">
        <?php $prevPage = max(1, (int) ($pagination['page'] ?? 1) - 1); ?>
        <?php $nextPage = min((int) ($pagination['total_pages'] ?? 1), (int) ($pagination['page'] ?? 1) + 1); ?>
        <button type="button" data-inquiry-page="<?= $prevPage ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Prev</button>
        <button type="button" data-inquiry-page="<?= $nextPage ?>" class="rounded border border-slate-300 px-3 py-1.5 <?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'pointer-events-none opacity-40' : '' ?>">Next</button>
    </div>
</div>