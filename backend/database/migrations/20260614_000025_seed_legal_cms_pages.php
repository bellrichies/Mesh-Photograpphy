<?php
declare(strict_types=1);

return new class {
    public string $description = 'Seed Terms of Service and Privacy Policy CMS pages';

    public function up(\PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        $pages = [
            [
                'title'           => 'Terms of Service',
                'slug'            => 'terms',
                'body'            => '<h2>Terms of Service</h2>
<p>Welcome to Mesh Photography. By accessing or using our website and services, you agree to be bound by these Terms of Service. Please read them carefully before using our site.</p>

<h3>1. Services</h3>
<p>Mesh Photography provides professional photography services including but not limited to editorial, portrait, wedding, and corporate photography. The terms of any specific photography engagement are governed by a separate service agreement.</p>

<h3>2. Intellectual Property</h3>
<p>All photographs, images, and content produced by Mesh Photography remain the intellectual property of Mesh Photography unless explicitly transferred in writing. Clients receive a limited license to use delivered images for the purposes outlined in their service agreement.</p>

<h3>3. Bookings and Cancellations</h3>
<p>Booking requests submitted through our website are subject to availability and confirmation by our team. Cancellation policies are outlined in your individual service agreement. We reserve the right to cancel bookings due to unforeseen circumstances.</p>

<h3>4. Limitation of Liability</h3>
<p>Mesh Photography shall not be liable for any indirect, incidental, or consequential damages arising from use of our website or services beyond the fees paid for the applicable service.</p>

<h3>5. Privacy</h3>
<p>Your use of this website is also governed by our Privacy Policy, which is incorporated herein by reference.</p>

<h3>6. Changes to Terms</h3>
<p>We reserve the right to update these Terms of Service at any time. Continued use of our website following any changes constitutes acceptance of those changes.</p>

<h3>7. Contact</h3>
<p>If you have any questions about these Terms of Service, please contact us at <a href="mailto:hello@meshphotography.com">hello@meshphotography.com</a>.</p>',
                'seo_title'       => 'Terms of Service | Mesh Photography',
                'seo_description' => 'Read the Terms of Service for Mesh Photography — professional photography services.',
                'is_published'    => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'title'           => 'Privacy Policy',
                'slug'            => 'privacy-policy',
                'body'            => '<h2>Privacy Policy</h2>
<p>At Mesh Photography, we respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, and safeguard your information when you visit our website or use our services.</p>

<h3>1. Information We Collect</h3>
<p>We may collect information you provide directly to us, such as when you submit a contact form, booking request, or newsletter subscription. This may include your name, email address, phone number, and any other details you choose to share.</p>

<h3>2. How We Use Your Information</h3>
<p>We use the information we collect to respond to your inquiries, process booking requests, send service-related communications, and improve our website and services. We do not sell your personal information to third parties.</p>

<h3>3. Cookies</h3>
<p>Our website may use cookies and similar tracking technologies to enhance your browsing experience. You can control cookie settings through your browser preferences.</p>

<h3>4. Data Security</h3>
<p>We implement appropriate technical and organisational measures to protect your personal data against unauthorised access, alteration, disclosure, or destruction.</p>

<h3>5. Your Rights</h3>
<p>Depending on your jurisdiction, you may have the right to access, correct, or delete your personal data. To exercise these rights, please contact us at <a href="mailto:hello@meshphotography.com">hello@meshphotography.com</a>.</p>

<h3>6. Changes to This Policy</h3>
<p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting the updated policy on this page with a revised date.</p>

<h3>7. Contact</h3>
<p>If you have questions about this Privacy Policy, please contact us at <a href="mailto:hello@meshphotography.com">hello@meshphotography.com</a>.</p>',
                'seo_title'       => 'Privacy Policy | Mesh Photography',
                'seo_description' => 'Read the Privacy Policy for Mesh Photography — how we collect and protect your personal information.',
                'is_published'    => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'title'           => 'Cookie Policy',
                'slug'            => 'cookie-policy',
                'body'            => '<h2>Cookie Policy</h2>
<p>This Cookie Policy explains how Mesh Photography uses cookies and similar technologies when you visit our website.</p>

<h3>What Are Cookies?</h3>
<p>Cookies are small text files stored on your device when you visit a website. They help us remember your preferences and understand how you use our site.</p>

<h3>How We Use Cookies</h3>
<p>We use cookies for essential website functionality, analytics to understand site usage, and to improve your overall experience. We do not use cookies for targeted advertising.</p>

<h3>Managing Cookies</h3>
<p>You can manage or disable cookies through your browser settings. Please note that disabling certain cookies may affect website functionality.</p>

<h3>Contact</h3>
<p>Questions about our use of cookies? Contact us at <a href="mailto:hello@meshphotography.com">hello@meshphotography.com</a>.</p>',
                'seo_title'       => 'Cookie Policy | Mesh Photography',
                'seo_description' => 'Read the Cookie Policy for Mesh Photography — how we use cookies on our website.',
                'is_published'    => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO pages (title, slug, body, seo_title, seo_description, is_published, created_at, updated_at)
            VALUES (:title, :slug, :body, :seo_title, :seo_description, :is_published, :created_at, :updated_at)
        ");

        foreach ($pages as $page) {
            $stmt->execute($page);
        }

        echo "[Migration] Seeded " . count($pages) . " legal CMS pages (terms, privacy-policy, cookie-policy).\n";
    }

    public function down(\PDO $pdo): void
    {
        $pdo->prepare("DELETE FROM pages WHERE slug IN ('terms', 'privacy-policy', 'cookie-policy')")
            ->execute();
    }
};
