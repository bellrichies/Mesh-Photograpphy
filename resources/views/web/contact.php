<?php
$values = isset($values) && is_array($values) ? $values : [];
$services = isset($services) && is_array($services) ? $services : [];
$page = isset($page) && is_array($page) ? $page : [];
$contactEmail = (string) ($contactEmail ?? '');
$contactPhone = (string) ($contactPhone ?? '');
$contactAddress = trim((string) ($contactAddress ?? ''));
$businessHours = isset($businessHours) && is_array($businessHours) ? $businessHours : [];
$budgetOptions = ['Under $2,000', '$2,000 - $4,000', '$4,000 - $7,500', '$7,500+', 'Custom scope'];
$referralOptions = ['Instagram', 'Google Search', 'Referral', 'Planner or Venue', 'Returning Client', 'Other'];
$heroBlock = published_reusable_block('contact-hero');
$processBlock = published_reusable_block('contact-process');
$formIntroBlockHtml = render_reusable_block('contact-form-intro');
$bookingCtaBlockHtml = render_reusable_block('contact-booking-cta');
$inquiryPromiseBlockHtml = render_reusable_block('inquiry-promise');
$heroTitle = (string) (($heroBlock['title'] ?? '') !== '' ? ($heroBlock['title'] ?? '') : (($page['title'] ?? '') !== '' ? ($page['title'] ?? '') : 'Start the conversation with the essentials already in place.'));
$heroDescription = (string) (($heroBlock['body'] ?? '') !== '' ? ($heroBlock['body'] ?? '') : (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : 'Use the inquiry form to share the project scope, timing, and context. Your message is stored directly for follow-up and communication history.'));
$pageIntro = trim((string) ($page['body'] ?? ''));
$processPayload = is_array($processBlock) ? json_decode((string) ($processBlock['json_payload'] ?? ''), true) : [];
$processPayload = is_array($processPayload) ? $processPayload : [];
$processEyebrow = (string) (($processPayload['eyebrow'] ?? '') !== '' ? ($processPayload['eyebrow'] ?? '') : 'How It Works');
$processTitle = (string) ((is_array($processBlock) && ($processBlock['title'] ?? '') !== '') ? ($processBlock['title'] ?? '') : 'From first message to confirmed booking');
$processDescription = (string) ((is_array($processBlock) && ($processBlock['body'] ?? '') !== '') ? ($processBlock['body'] ?? '') : '');
$businessHoursDays = [];
$businessHoursTimezone = trim((string) ($businessHours['timezone'] ?? ''));

if (isset($businessHours['days']) && is_array($businessHours['days'])) {
    $businessHoursDays = $businessHours['days'];
} elseif ($businessHours !== []) {
    foreach ($businessHours as $label => $hours) {
        if ($label === 'timezone') {
            continue;
        }

        $businessHoursDays[] = [
            'label' => ucwords(str_replace(['-', '_'], ' ', (string) $label)),
            'hours' => (string) $hours,
        ];
    }
}

$hoursSummaryParts = [];
foreach (array_slice($businessHoursDays, 0, 2) as $day) {
    $label = trim((string) ($day['label'] ?? ''));
    $hours = trim((string) ($day['hours'] ?? ''));
    if ($label !== '' && $hours !== '') {
        $hoursSummaryParts[] = $label . ': ' . $hours;
    }
}

$hoursSummary = implode(' | ', $hoursSummaryParts);
$processSteps = isset($processPayload['steps']) && is_array($processPayload['steps']) ? $processPayload['steps'] : [
    [
        'number' => '1',
        'title' => 'Share Your Vision',
        'body' => 'Fill out the inquiry form with your project type, timing, location, and any planning details.',
    ],
    [
        'number' => '2',
        'title' => 'Review & Reply',
        'body' => 'Your inquiry is stored in the studio\'s system. You\'ll receive a confirmation and a detailed response.',
    ],
    [
        'number' => '3',
        'title' => 'Plan & Create',
        'body' => 'Once the scope is aligned, the project moves into scheduling, preparation, and creative direction.',
    ],
];
?>

<!-- Hero -->
<section class="page-hero" style="min-height:32vh">
    <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars(asset_url('assets/images/contact-hero.jpg'), ENT_QUOTES, 'UTF-8') ?>'); opacity: 0.2;"></div>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Contact'],
        ];
        $variant = 'dark';
        include dirname(__DIR__) . '/partials/breadcrumbs.php';
        ?>

        <div class="mt-4 grid items-end gap-10 lg:grid-cols-2">
            <div class="reveal">
                <p class="page-hero-eyebrow">Get in Touch</p>
                <h1 class="page-hero-title"><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="page-hero-desc"><?= htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="reveal reveal-delay-2 grid gap-3 sm:grid-cols-2">
                <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                    <div class="info-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-white/50">Email</p>
                        <p class="mt-1 text-sm text-white/80"><?= htmlspecialchars($contactEmail !== '' ? $contactEmail : 'Configured in settings', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                    <div class="info-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-white/50">Phone</p>
                        <p class="mt-1 text-sm text-white/80"><?= htmlspecialchars($contactPhone !== '' ? $contactPhone : 'Available on request', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <?php if ($contactAddress !== ''): ?>
                    <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                        <div class="info-card-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c4.97-4.97 7.5-8.73 7.5-11.25a7.5 7.5 0 10-15 0C4.5 12.27 7.03 16.03 12 21z"/><circle cx="12" cy="9.75" r="2.25"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-white/50">Studio</p>
                            <p class="mt-1 text-sm text-white/80"><?= nl2br(htmlspecialchars($contactAddress, ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($hoursSummary !== '' || $businessHoursTimezone !== ''): ?>
                    <div class="info-card" style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1)">
                        <div class="info-card-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-white/50">Hours</p>
                            <?php if ($hoursSummary !== ''): ?><p class="mt-1 text-sm text-white/80"><?= htmlspecialchars($hoursSummary, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            <?php if ($businessHoursTimezone !== ''): ?><p class="mt-1 text-xs text-white/55"><?= htmlspecialchars($businessHoursTimezone, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Process Steps -->
<section class="section-md" style="background: var(--color-ivory-warm);">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="section-label"><?= htmlspecialchars($processEyebrow, ENT_QUOTES, 'UTF-8') ?></p>
            <h2 class="mt-3 text-display-md"><?= htmlspecialchars($processTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ($processDescription !== ''): ?><p class="mt-3 mx-auto max-w-2xl text-body-md text-charcoal/65"><?= htmlspecialchars($processDescription, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            <?php foreach (array_slice($processSteps, 0, 3) as $index => $step): ?>
                <div class="reveal reveal-delay-<?= min($index + 1, 4) ?> text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-charcoal text-ivory font-display text-lg"><?= htmlspecialchars((string) (($step['number'] ?? '') !== '' ? ($step['number'] ?? '') : (string) ($index + 1)), ENT_QUOTES, 'UTF-8') ?></div>
                    <h3 class="mt-4 font-display text-xl text-charcoal"><?= htmlspecialchars((string) ($step['title'] ?? 'Step'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-sm leading-relaxed text-charcoal/65"><?= htmlspecialchars((string) ($step['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Inquiry Form -->
<section class="section-lg">
    <div class="container">
        <div class="mx-auto max-w-4xl">
            <div class="reveal mb-10 text-center">
                <p class="section-label">Inquiry Form</p>
                <h2 class="mt-3 text-display-md">Project details, availability &amp; context</h2>
                <p class="mt-3 mx-auto max-w-xl text-body-md text-charcoal/65">Take a moment to share the essentials; it helps shape a more thoughtful and relevant first reply.</p>
            </div>

            <?php if ($formIntroBlockHtml !== ''): ?>
                <div class="mb-8 reveal">
                    <?= $formIntroBlockHtml ?>
                </div>
            <?php elseif ($pageIntro !== ''): ?>
                <div class="mb-8 reveal mx-auto max-w-3xl text-center">
                    <p class="text-body-md leading-8 text-charcoal/70"><?= nl2br(htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="reveal" novalidate>
                <?= csrf_field() ?>

                <!-- Honeypot -->
                <div class="hidden" aria-hidden="true">
                    <label for="contact-website">Website</label>
                    <input id="contact-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="contact-first-name" class="form-label">First Name</label>
                        <input id="contact-first-name" type="text" name="first_name" value="<?= htmlspecialchars((string) ($values['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Jane" required autocomplete="given-name">
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="contact-last-name" class="form-label">Last Name</label>
                        <input id="contact-last-name" type="text" name="last_name" value="<?= htmlspecialchars((string) ($values['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Williams" required autocomplete="family-name">
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="contact-email" class="form-label">Email</label>
                        <input id="contact-email" type="email" name="email" value="<?= htmlspecialchars((string) ($values['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="jane@example.com" required autocomplete="email" inputmode="email" aria-describedby="contact-email-help">
                        <p id="contact-email-help" class="form-error" style="color:var(--color-charcoal-40);margin-top:0.5rem">Used for the reply and confirmation workflow.</p>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="contact-phone" class="form-label">Phone</label>
                        <input id="contact-phone" type="tel" name="phone" value="<?= htmlspecialchars((string) ($values['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="+1 (555) 000-0000" autocomplete="tel">
                    </div>

                    <!-- Company -->
                    <div class="form-group">
                        <label for="contact-company-name" class="form-label">Company Name</label>
                        <input id="contact-company-name" type="text" name="company_name" value="<?= htmlspecialchars((string) ($values['company_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="Optional" autocomplete="organization">
                    </div>

                    <!-- Service Interest -->
                    <div class="form-group">
                        <label for="contact-service-interest" class="form-label">Service Interest</label>
                        <select id="contact-service-interest" name="service_interest" class="form-select">
                            <option value="">Select a service</option>
                            <?php foreach ($services as $service): ?>
                                <?php $serviceTitle = (string) ($service['title'] ?? ''); ?>
                                <option value="<?= htmlspecialchars($serviceTitle, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['service_interest'] ?? '') === $serviceTitle ? 'selected' : '' ?>><?= htmlspecialchars($serviceTitle, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                            <option value="Other" <?= (string) ($values['service_interest'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <!-- Preferred Date -->
                    <div class="form-group">
                        <label for="contact-preferred-date" class="form-label">Preferred Date</label>
                        <input id="contact-preferred-date" type="date" name="preferred_date" value="<?= htmlspecialchars((string) ($values['preferred_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input">
                    </div>

                    <!-- Budget -->
                    <div class="form-group">
                        <label for="contact-budget-range" class="form-label">Budget Range</label>
                        <select id="contact-budget-range" name="budget_range" class="form-select">
                            <option value="">Select a budget range</option>
                            <?php foreach ($budgetOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['budget_range'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="contact-location" class="form-label">Location</label>
                        <input id="contact-location" type="text" name="location" value="<?= htmlspecialchars((string) ($values['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="form-input" placeholder="City or venue" autocomplete="address-level2">
                    </div>

                    <!-- Referral Source -->
                    <div class="form-group">
                        <label for="contact-referral-source" class="form-label">How did you hear about us?</label>
                        <select id="contact-referral-source" name="referral_source" class="form-select">
                            <option value="">Select a source</option>
                            <?php foreach ($referralOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($values['referral_source'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Message -->
                    <div class="form-group sm:col-span-2">
                        <label for="contact-message" class="form-label">Message</label>
                        <textarea id="contact-message" name="message" rows="6" class="form-textarea" placeholder="Share the timeline, venue, audience, and any details that help shape the conversation&hellip;" required aria-describedby="contact-message-help"><?= htmlspecialchars((string) ($values['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <p id="contact-message-help" class="mt-2 text-xs text-charcoal/45">The more context you provide, the more relevant the initial reply.</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    <div class="text-xs text-charcoal/40">
                        <?php if ($inquiryPromiseBlockHtml !== ''): ?>
                            <?= $inquiryPromiseBlockHtml ?>
                        <?php else: ?>
                            <p>All fields are kept confidential and stored securely.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Send Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Booking Alternative CTA -->
<?php if ($bookingCtaBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $bookingCtaBlockHtml ?>
    </div>
</section>
<?php else: ?>
<section class="section-md" style="background: var(--color-charcoal);">
    <div class="container">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="section-label text-bronze/80">Alternative</p>
            <h2 class="mt-3 text-display-md text-ivory">Prefer a direct booking request?</h2>
            <p class="mt-3 text-body-md text-ivory/65">If you already have dates and scope in mind, skip the inquiry and go straight to a structured booking request.</p>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <a href="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary bg-ivory text-charcoal hover:bg-white">Request Availability</a>
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary border-white/15 text-ivory hover:border-white/30">View Portfolio</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
