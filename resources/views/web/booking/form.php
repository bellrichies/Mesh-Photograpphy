<?php
$values = isset($values) && is_array($values) ? $values : [];
$services = isset($services) && is_array($services) ? $services : [];
$selectedService = isset($selectedService) && is_array($selectedService) ? $selectedService : null;
$linkedInquiry = isset($linkedInquiry) && is_array($linkedInquiry) ? $linkedInquiry : null;
$eventTypes = ['Wedding', 'Engagement Session', 'Portrait Session', 'Family Session', 'Brand Session', 'Editorial Project', 'Event Coverage', 'Other'];
$selectedServiceTitle = trim((string) ($selectedService['title'] ?? ''));
$linkedInquiryId = (int) ($linkedInquiry['id'] ?? 0);
$requestDate = trim((string) ($values['requested_date'] ?? ''));
$requestLocation = trim((string) ($values['location'] ?? ''));
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<section class="page-hero" style="background: var(--color-charcoal); min-height: 24vh;">
    <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars(asset_url('assets/images/contact-hero.jpg'), ENT_QUOTES, 'UTF-8') ?>'); opacity: 0.22;"></div>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Booking'],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="mt-4 grid items-end gap-8 lg:grid-cols-[1.2fr,0.8fr]">
            <div class="reveal">
                <p class="page-hero-eyebrow">Booking Request</p>
                <h1 class="page-hero-title">Reserve the date first, then shape the experience around it.</h1>
                <p class="page-hero-desc">Use this form to share availability, event context, and the practical details that help the studio assess fit quickly and respond with clarity.</p>
            </div>
            <div class="reveal reveal-delay-2 grid gap-3 sm:grid-cols-2">
                <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                    <div class="info-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-white/50">Response Rhythm</p>
                        <p class="mt-1 text-sm text-white/80">Availability requests are reviewed before planning or proposal details.</p>
                    </div>
                </div>
                <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                    <div class="info-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M7.5 3v3m9-3v3M6.75 21h10.5A2.25 2.25 0 0019.5 18.75V7.5A2.25 2.25 0 0017.25 5.25H6.75A2.25 2.25 0 004.5 7.5v11.25A2.25 2.25 0 006.75 21z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-white/50">Best For</p>
                        <p class="mt-1 text-sm text-white/80">Confirmed dates, active planning windows, and projects ready for scheduling.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-md" style="background: var(--color-ivory-warm);">
    <div class="container">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="reveal rounded-[1.75rem] border border-charcoal/10 bg-white px-6 py-6 shadow-[0_20px_50px_-36px_rgba(20,20,20,0.18)]">
                <p class="text-xs uppercase tracking-[0.22em] text-bronze">Selected Focus</p>
                <h2 class="mt-3 font-display text-3xl text-charcoal"><?= htmlspecialchars($selectedServiceTitle !== '' ? $selectedServiceTitle : 'Flexible Service Scope', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-3 text-sm leading-7 text-charcoal/68">Choose a service if one is already clear, or leave it open and use the notes field to describe the shape of the event or commission.</p>
            </div>
            <div class="reveal reveal-delay-2 rounded-[1.75rem] border border-charcoal/10 bg-white px-6 py-6 shadow-[0_20px_50px_-36px_rgba(20,20,20,0.18)]">
                <p class="text-xs uppercase tracking-[0.22em] text-bronze">Current Context</p>
                <div class="mt-3 space-y-2 text-sm leading-7 text-charcoal/70">
                    <p><span class="font-medium text-charcoal">Requested date:</span> <?= htmlspecialchars($requestDate !== '' ? $requestDate : 'Not set yet', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><span class="font-medium text-charcoal">Location:</span> <?= htmlspecialchars($requestLocation !== '' ? $requestLocation : 'To be confirmed', ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($linkedInquiryId > 0): ?>
                        <p><span class="font-medium text-charcoal">Linked inquiry:</span> #<?= $linkedInquiryId ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="reveal reveal-delay-3 rounded-[1.75rem] border border-charcoal/10 bg-charcoal px-6 py-6 text-ivory shadow-[0_24px_60px_-34px_rgba(20,20,20,0.42)]">
                <p class="text-xs uppercase tracking-[0.22em] text-bronze/90">What Helps Most</p>
                <ul class="mt-4 space-y-3 text-sm leading-7 text-ivory/76">
                    <li>Share the exact date or closest working target.</li>
                    <li>Add venue, city, or travel context where possible.</li>
                    <li>Use notes for timing, guest count nuance, or production considerations.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section-lg">
    <div class="container">
        <div class="grid gap-8 xl:grid-cols-[1.2fr,0.8fr]">
            <div class="reveal rounded-[2rem] border border-charcoal/10 bg-white px-7 py-8 shadow-[0_24px_70px_-40px_rgba(20,20,20,0.22)] lg:px-9">
                <div class="mb-8 flex flex-wrap items-start justify-between gap-4 border-b border-charcoal/8 pb-6">
                    <div class="max-w-2xl">
                        <p class="text-xs uppercase tracking-[0.22em] text-bronze">Availability Form</p>
                        <h2 class="mt-3 font-display text-4xl text-charcoal">Event details with enough depth to review the date well.</h2>
                        <p class="mt-3 text-sm leading-7 text-charcoal/68">The form stays intentionally structured so the first reply can be fast, accurate, and grounded in the actual scope of the work.</p>
                    </div>
                    <?php if ($linkedInquiryId > 0): ?>
                        <div class="rounded-full bg-[#f4ede5] px-4 py-2 text-[11px] font-medium uppercase tracking-[0.16em] text-charcoal/72">Linked to inquiry #<?= $linkedInquiryId ?></div>
                    <?php endif; ?>
                </div>

                <form method="post" action="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="grid gap-x-6 gap-y-5 lg:grid-cols-2" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars((string) ($values['inquiry_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="booking-first-name" class="form-label">First Name</label>
                        <input id="booking-first-name" type="text" name="first_name" value="<?= htmlspecialchars((string) ($values['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" required autocomplete="given-name">
                    </div>

                    <div class="form-group">
                        <label for="booking-last-name" class="form-label">Last Name</label>
                        <input id="booking-last-name" type="text" name="last_name" value="<?= htmlspecialchars((string) ($values['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" required autocomplete="family-name">
                    </div>

                    <div class="form-group">
                        <label for="booking-email" class="form-label">Email</label>
                        <input id="booking-email" type="email" name="email" value="<?= htmlspecialchars((string) ($values['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" required autocomplete="email" inputmode="email">
                    </div>

                    <div class="form-group">
                        <label for="booking-phone" class="form-label">Phone</label>
                        <input id="booking-phone" type="tel" name="phone" value="<?= htmlspecialchars((string) ($values['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" autocomplete="tel">
                    </div>

                    <div class="form-group">
                        <label for="booking-service" class="form-label">Service</label>
                        <select id="booking-service" name="service_id" class="form-select">
                            <option value="">Select a service</option>
                            <?php foreach ($services as $service): ?>
                                <?php $serviceId = (string) ($service['id'] ?? ''); ?>
                                <option value="<?= htmlspecialchars($serviceId, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['service_id'] ?? '') === $serviceId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="booking-event-type" class="form-label">Event Type</label>
                        <select id="booking-event-type" name="event_type" class="form-select" required>
                            <option value="">Select an event type</option>
                            <?php foreach ($eventTypes as $eventType): ?>
                                <option value="<?= htmlspecialchars($eventType, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['event_type'] ?? '') === $eventType ? 'selected' : '' ?>><?= htmlspecialchars($eventType, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="booking-requested-date" class="form-label">Requested Date</label>
                        <input id="booking-requested-date" type="text" name="requested_date" value="<?= htmlspecialchars((string) ($values['requested_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Select a date" required aria-describedby="booking-requested-date-help">
                        <p id="booking-requested-date-help" class="mt-2 text-xs leading-6 text-charcoal/50">Use the ideal date or the nearest working target if the calendar is still being finalized.</p>
                    </div>

                    <div class="form-group">
                        <label for="booking-requested-time" class="form-label">Requested Time</label>
                        <input id="booking-requested-time" type="text" name="requested_time" value="<?= htmlspecialchars((string) ($values['requested_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Optional time window">
                    </div>

                    <div class="form-group">
                        <label for="booking-location" class="form-label">Location</label>
                        <input id="booking-location" type="text" name="location" value="<?= htmlspecialchars((string) ($values['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" required autocomplete="address-level2">
                    </div>

                    <div class="form-group">
                        <label for="booking-hours-needed" class="form-label">Hours Needed</label>
                        <input id="booking-hours-needed" type="number" min="0" step="0.5" name="hours_needed" value="<?= htmlspecialchars((string) ($values['hours_needed'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Optional" inputmode="decimal">
                    </div>

                    <div class="form-group lg:col-span-2">
                        <label for="booking-guest-count" class="form-label">Guest Count</label>
                        <input id="booking-guest-count" type="number" min="0" step="1" name="guest_count" value="<?= htmlspecialchars((string) ($values['guest_count'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Optional" inputmode="numeric">
                    </div>

                    <div class="form-group lg:col-span-2">
                        <label for="booking-notes" class="form-label">Notes</label>
                        <textarea id="booking-notes" name="notes" rows="7" class="form-textarea" placeholder="Share timing, venue considerations, travel, styling needs, access restrictions, or anything else that affects scheduling." aria-describedby="booking-notes-help"><?= htmlspecialchars((string) ($values['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <p id="booking-notes-help" class="mt-2 text-xs leading-6 text-charcoal/50">Useful notes include ceremony timing, reception structure, production load-in, permit questions, or destination travel context.</p>
                    </div>

                    <div class="lg:col-span-2 flex flex-col gap-4 border-t border-charcoal/8 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-6 text-charcoal/45">Your request is stored directly in the admin workflow so availability review and follow-up can stay connected.</p>
                        <button type="submit" class="btn-primary">
                            <span>Submit Booking Request</span>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="reveal reveal-delay-2 rounded-[1.9rem] border border-charcoal/10 bg-[#fbf8f4] px-6 py-6 shadow-[0_18px_50px_-38px_rgba(20,20,20,0.18)]">
                    <p class="text-xs uppercase tracking-[0.22em] text-bronze">How This Works</p>
                    <div class="mt-5 space-y-5">
                        <div class="rounded-[1.2rem] border border-charcoal/8 bg-white px-4 py-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-charcoal/45">Step 1</p>
                            <h3 class="mt-2 font-display text-2xl text-charcoal">Check the date</h3>
                            <p class="mt-2 text-sm leading-7 text-charcoal/68">The first pass is availability and logistics, so clear timing and location details help immediately.</p>
                        </div>
                        <div class="rounded-[1.2rem] border border-charcoal/8 bg-white px-4 py-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-charcoal/45">Step 2</p>
                            <h3 class="mt-2 font-display text-2xl text-charcoal">Review the scope</h3>
                            <p class="mt-2 text-sm leading-7 text-charcoal/68">Once the date is viable, the studio can reply with the next planning step, fit notes, and collection direction.</p>
                        </div>
                        <div class="rounded-[1.2rem] border border-charcoal/8 bg-white px-4 py-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-charcoal/45">Step 3</p>
                            <h3 class="mt-2 font-display text-2xl text-charcoal">Move into planning</h3>
                            <p class="mt-2 text-sm leading-7 text-charcoal/68">If it aligns, the conversation shifts into scheduling, coverage strategy, and preparation.</p>
                        </div>
                    </div>
                </div>

                <div class="reveal reveal-delay-3 rounded-[1.9rem] border border-charcoal/10 bg-charcoal px-6 py-6 text-ivory shadow-[0_24px_60px_-34px_rgba(20,20,20,0.44)]">
                    <p class="text-xs uppercase tracking-[0.22em] text-bronze/90">Good Inputs</p>
                    <div class="mt-4 space-y-4 text-sm leading-7 text-ivory/78">
                        <p>Venue city, guest count range, and timing windows are more helpful than long generic descriptions.</p>
                        <p>If the project is still taking shape, use the notes field to explain what is known now and what is still in motion.</p>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary border-white/15 text-ivory hover:border-white/30">Need a fuller inquiry?</a>
                        <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary border-white/15 text-ivory hover:border-white/30">View Portfolio</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    if (window.flatpickr) {
        window.flatpickr('#booking-requested-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });

        window.flatpickr('#booking-requested-time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true
        });
    }
</script>
