<?php

declare(strict_types=1);

namespace Database\Seeders;

class DemoContentFactory
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function users(): array
    {
        return [
            [
                'first_name' => 'Ava',
                'last_name' => 'Stone',
                'email' => 'ava.stone@mesh.local',
                'password' => 'Password123!',
                'status' => 'active',
                'role' => 'super-admin',
            ],
            [
                'first_name' => 'Nolan',
                'last_name' => 'Reyes',
                'email' => 'nolan.reyes@mesh.local',
                'password' => 'Password123!',
                'status' => 'active',
                'role' => 'editor',
            ],
            [
                'first_name' => 'Mia',
                'last_name' => 'Carter',
                'email' => 'mia.carter@mesh.local',
                'password' => 'Password123!',
                'status' => 'active',
                'role' => 'content-manager',
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rolePermissions(): array
    {
        return [
            'super-admin' => [
                'manage-users',
                'manage-settings',
                'manage-media',
                'manage-pages',
                'manage-galleries',
                'manage-services',
                'manage-blog',
                'manage-testimonials',
                'manage-inquiries',
            ],
            'editor' => [
                'manage-pages',
                'manage-blog',
                'manage-galleries',
                'manage-testimonials',
            ],
            'content-manager' => [
                'manage-settings',
                'manage-media',
                'manage-pages',
                'manage-galleries',
                'manage-services',
                'manage-testimonials',
                'manage-inquiries',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function media(): array
    {
        return [
            [
                'uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'original_name' => 'mesh-hero-wedding.jpg',
                'stored_name' => 'mesh-hero-wedding.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 648320,
                'width' => 3200,
                'height' => 2133,
                'alt_text' => 'Couple walking through evening city lights after their ceremony.',
                'title' => 'Downtown Afterglow',
                'caption' => 'A twilight city portrait with editorial direction.',
                'description' => 'Primary homepage hero and wedding campaign image.',
                'checksum' => hash('sha256', 'mesh-hero-wedding.jpg'),
                'uploaded_by' => 'ava.stone@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'mesh-hero-wedding-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 92214,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => '4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8',
                'original_name' => 'coast-vows.jpg',
                'stored_name' => 'coast-vows.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 593412,
                'width' => 2800,
                'height' => 1867,
                'alt_text' => 'Ceremony portraits on a windy coastline.',
                'title' => 'Coastline Vows',
                'caption' => 'An oceanfront ceremony with quiet movement and soft haze.',
                'description' => 'Featured portfolio gallery cover.',
                'checksum' => hash('sha256', 'coast-vows.jpg'),
                'uploaded_by' => 'ava.stone@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'coast-vows-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 85410,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'original_name' => 'atelier-portrait-study.jpg',
                'stored_name' => 'atelier-portrait-study.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 481220,
                'width' => 2400,
                'height' => 3000,
                'alt_text' => 'Studio portrait with directional light and neutral styling.',
                'title' => 'Atelier Portrait Study',
                'caption' => 'A portrait session focused on shape, tone, and restraint.',
                'description' => 'Portrait service and testimonial image.',
                'checksum' => hash('sha256', 'atelier-portrait-study.jpg'),
                'uploaded_by' => 'nolan.reyes@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'atelier-portrait-study-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 76288,
                        'width' => 768,
                        'height' => 960,
                    ],
                ],
            ],
            [
                'uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'original_name' => 'maison-brand-story.jpg',
                'stored_name' => 'maison-brand-story.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 522804,
                'width' => 2800,
                'height' => 1867,
                'alt_text' => 'Brand founder arranging product styling in a bright studio.',
                'title' => 'Maison Brand Story',
                'caption' => 'A branding session with product detail and quiet pacing.',
                'description' => 'Brand editorial service cover and homepage section media.',
                'checksum' => hash('sha256', 'maison-brand-story.jpg'),
                'uploaded_by' => 'mia.carter@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'maison-brand-story-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 80114,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => 'd7a1a1d7-eb76-4b7d-b76c-36c8fdb7b54d',
                'original_name' => 'family-garden.jpg',
                'stored_name' => 'family-garden.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 437108,
                'width' => 2600,
                'height' => 1733,
                'alt_text' => 'Family session in a garden with natural movement.',
                'title' => 'Garden Family Session',
                'caption' => 'Relaxed portrait work with movement, texture, and warmth.',
                'description' => 'Lifestyle portrait gallery image.',
                'checksum' => hash('sha256', 'family-garden.jpg'),
                'uploaded_by' => 'mia.carter@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'family-garden-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 73544,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => '293db6c0-6ae3-44a3-9e4e-4ea1d5f3c492',
                'original_name' => 'reception-candids.jpg',
                'stored_name' => 'reception-candids.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 563808,
                'width' => 3000,
                'height' => 2000,
                'alt_text' => 'Guests laughing during reception speeches.',
                'title' => 'Reception Candids',
                'caption' => 'Documentary frames from a fast-moving celebration.',
                'description' => 'Supplemental wedding gallery image.',
                'checksum' => hash('sha256', 'reception-candids.jpg'),
                'uploaded_by' => 'ava.stone@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'reception-candids-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 90424,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => 'd3bfdbb4-c919-4354-af76-ad6515e5f3df',
                'original_name' => 'flatlay-details.jpg',
                'stored_name' => 'flatlay-details.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 325410,
                'width' => 2400,
                'height' => 1800,
                'alt_text' => 'Wedding invitation suite and floral styling arranged on linen.',
                'title' => 'Flatlay Details',
                'caption' => 'A styled still life with paper goods, texture, and color restraint.',
                'description' => 'Blog support image for planning and details coverage.',
                'checksum' => hash('sha256', 'flatlay-details.jpg'),
                'uploaded_by' => 'nolan.reyes@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'flatlay-details-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 68410,
                        'width' => 960,
                        'height' => 720,
                    ],
                ],
            ],
            [
                'uuid' => '8c2b7ca2-2213-49d6-8a02-3272efb3c4fb',
                'original_name' => 'journal-workspace.jpg',
                'stored_name' => 'journal-workspace.jpg',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 298122,
                'width' => 2200,
                'height' => 1467,
                'alt_text' => 'Editorial planning desk with notebooks, proofs, and coffee.',
                'title' => 'Journal Workspace',
                'caption' => 'Behind-the-scenes planning tools in the studio.',
                'description' => 'Journal and planning article image.',
                'checksum' => hash('sha256', 'journal-workspace.jpg'),
                'uploaded_by' => 'nolan.reyes@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'journal-workspace-thumb.jpg',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/jpeg',
                        'extension' => 'jpg',
                        'size_bytes' => 54116,
                        'width' => 960,
                        'height' => 640,
                    ],
                ],
            ],
            [
                'uuid' => 'e1a9c91d-9503-4c2d-a15a-0da1685b91fb',
                'original_name' => 'mesh-logo-mark.png',
                'stored_name' => 'mesh-logo-mark.png',
                'directory' => 'public/uploads/images/2026/03',
                'extension' => 'png',
                'mime_type' => 'image/png',
                'file_type' => 'image',
                'size_bytes' => 48210,
                'width' => 1200,
                'height' => 1200,
                'alt_text' => 'Mesh Photography logo mark.',
                'title' => 'Mesh Logo Mark',
                'caption' => 'Square brand mark for SEO and global settings.',
                'description' => 'Brand logo asset used in settings.',
                'checksum' => hash('sha256', 'mesh-logo-mark.png'),
                'uploaded_by' => 'mia.carter@mesh.local',
                'variants' => [
                    [
                        'variant_key' => 'thumb',
                        'stored_name' => 'mesh-logo-mark-thumb.png',
                        'directory' => 'public/uploads/images/2026/03/variants',
                        'mime_type' => 'image/png',
                        'extension' => 'png',
                        'size_bytes' => 14210,
                        'width' => 320,
                        'height' => 320,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function settings(): array
    {
        return [
            ['group' => 'general', 'key' => 'site_name', 'value_text' => 'Mesh Photography', 'is_public' => 1],
            ['group' => 'general', 'key' => 'tagline', 'value_text' => 'Editorial imagery for modern celebrations, portraits, and brands.', 'is_public' => 1],
            ['group' => 'general', 'key' => 'footer_text', 'value_text' => 'Serving couples, founders, and families with calm direction, thoughtful pacing, and imagery designed to feel timeless.', 'is_public' => 1],
            ['group' => 'general', 'key' => 'site_status', 'value_text' => 'live', 'is_public' => 1],
            ['group' => 'general', 'key' => 'show_author_credit', 'value_text' => '0', 'is_public' => 1],

            ['group' => 'contact', 'key' => 'contact_email', 'value_text' => 'hello@meshphotography.com', 'is_public' => 1],
            ['group' => 'contact', 'key' => 'phone', 'value_text' => '+1 (202) 555-0198', 'is_public' => 1],
            ['group' => 'contact', 'key' => 'address', 'value_text' => "Mesh Photography Studio\n1458 Penn Quarter Loft\nWashington, DC 20001\nUnited States", 'is_public' => 1],
            ['group' => 'contact', 'key' => 'business_hours', 'value_json' => [
                'timezone' => 'America/New_York',
                'days' => [
                    ['label' => 'Monday - Thursday', 'hours' => '10:00 AM - 5:00 PM'],
                    ['label' => 'Friday', 'hours' => '10:00 AM - 3:00 PM'],
                    ['label' => 'Saturday', 'hours' => 'By appointment'],
                    ['label' => 'Sunday', 'hours' => 'Closed'],
                ],
            ], 'is_public' => 1],

            ['group' => 'social', 'key' => 'instagram_url', 'value_text' => 'https://instagram.com/meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'behance_url', 'value_text' => 'https://behance.net/meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'youtube_url', 'value_text' => 'https://youtube.com/@meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'social_links', 'value_json' => [
                'instagram' => 'https://instagram.com/meshphotography',
                'behance' => 'https://behance.net/meshphotography',
                'youtube' => 'https://youtube.com/@meshphotography',
                'pinterest' => 'https://pinterest.com/meshphotography',
            ], 'is_public' => 1],

            ['group' => 'branding', 'key' => 'brand_site_title', 'value_text' => 'Mesh Photography', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'logo_alt_text', 'value_text' => 'Mesh Photography logo mark', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'brand_primary_color', 'value_text' => '#9A7B5C', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'brand_secondary_color', 'value_text' => '#1A1A1A', 'is_public' => 1],

            ['group' => 'email', 'key' => 'mailer_from_name', 'value_text' => 'Mesh Photography', 'is_public' => 0],
            ['group' => 'email', 'key' => 'mailer_from_email', 'value_text' => 'hello@meshphotography.com', 'is_public' => 0],
            ['group' => 'email', 'key' => 'mailer_reply_to', 'value_text' => 'hello@meshphotography.com', 'is_public' => 0],
            ['group' => 'email', 'key' => 'inquiry_recipients', 'value_json' => ['hello@meshphotography.com', 'bookings@meshphotography.com'], 'is_public' => 0],
            ['group' => 'email', 'key' => 'mail_enabled', 'value_text' => '1', 'is_public' => 0],

            ['group' => 'uploads', 'key' => 'max_file_size_mb', 'value_text' => '20', 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'allowed_mime_groups', 'value_json' => ['images', 'documents', 'videos'], 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'max_image_width', 'value_text' => '6000', 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'max_image_height', 'value_text' => '6000', 'is_public' => 0],

            ['group' => 'seo', 'key' => 'default_meta_title_pattern', 'value_text' => '{{title}} | {{site_name}}', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_meta_description', 'value_text' => 'Mesh Photography creates refined visual narratives for weddings, portraits, and thoughtful brands.', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_robots_index', 'value_text' => '1', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_meta_keywords', 'value_json' => [
                'editorial wedding photographer washington dc',
                'portrait photographer washington dc',
                'brand photography studio washington dc',
                'luxury wedding photography',
                'editorial portrait sessions',
            ], 'is_public' => 1],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function heroSlides(): array
    {
        return [
            [
                'title' => 'Editorial wedding coverage with calm direction and cinematic warmth',
                'subtitle' => 'Wedding Weekends',
                'description' => 'For couples who want the full weekend documented with polish, emotional accuracy, and a gallery that still feels alive years later.',
                'image_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'image_alt_text' => 'Couple walking through evening city lights after their ceremony.',
                'primary_cta_label' => 'Inquire for Your Date',
                'primary_cta_url' => '/contact',
                'secondary_cta_label' => 'View Wedding Work',
                'secondary_cta_url' => '/services/wedding-weekend-coverage',
                'sort_order' => 1,
                'status' => 'published',
            ],
            [
                'title' => 'Portrait sessions that feel refined, confident, and unmistakably personal',
                'subtitle' => 'Portrait Direction',
                'description' => 'A guided portrait experience built for founders, creatives, and individuals who want modern images without looking overly posed or overproduced.',
                'image_media_uuid' => 'd7a1a1d7-eb76-4b7d-b76c-36c8fdb7b54d',
                'image_alt_text' => 'Family session in a garden with natural movement.',
                'primary_cta_label' => 'Book a Portrait Session',
                'primary_cta_url' => '/contact',
                'secondary_cta_label' => 'Explore Portrait Services',
                'secondary_cta_url' => '/services/portrait-direction',
                'sort_order' => 2,
                'status' => 'published',
            ],
            [
                'title' => 'Brand imagery planned like a content system, not just a photo day',
                'subtitle' => 'Brand Editorials',
                'description' => 'We create launch-ready image libraries for thoughtful brands, with anchor frames, utility assets, and atmospheric detail that work across every channel.',
                'image_media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'image_alt_text' => 'Brand founder arranging product styling in a bright studio.',
                'primary_cta_label' => 'Plan a Brand Shoot',
                'primary_cta_url' => '/contact',
                'secondary_cta_label' => 'See Brand Editorials',
                'secondary_cta_url' => '/services/brand-editorials',
                'sort_order' => 3,
                'status' => 'published',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pages(): array
    {
        return [
            [
                'title' => 'Home',
                'slug' => 'home',
                'template' => 'homepage',
                'status' => 'published',
                'excerpt' => 'Refined visual storytelling for weddings, portraits, and modern brands.',
                'body' => null,
                'featured_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'is_system' => 1,
            ],
            [
                'title' => 'About',
                'slug' => 'about',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'The studio approach, pacing, and aesthetic behind Mesh Photography.',
                'body' => "Mesh Photography was built around a simple idea: premium imagery should feel calm to create and timeless to revisit. We photograph weddings, portraits, and brand stories with equal attention to atmosphere, movement, and editorial clarity.\n\nOur process is collaborative without becoming noisy. We guide where guidance matters, observe when the moment is already strong, and build visual continuity from first inquiry through final delivery.",
                'featured_media_uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'is_system' => 0,
            ],
            [
                'title' => 'Services',
                'slug' => 'services',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'Signature collections for wedding weekends, portrait commissions, and brand editorials.',
                'body' => "Each engagement begins with planning, atmosphere mapping, and a clear production rhythm. Whether we are documenting a wedding weekend or shaping a brand story, the goal is the same: images that feel intentional rather than generic.",
                'featured_media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'is_system' => 0,
            ],
            [
                'title' => 'Portfolio',
                'slug' => 'portfolio',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'Selected galleries from recent commissions and personal projects.',
                'body' => "The portfolio is organized as complete stories rather than disconnected highlight frames. Each gallery is designed to show pacing, tone, and the way a full experience actually unfolds.",
                'featured_media_uuid' => '4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8',
                'is_system' => 0,
            ],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'Planning notes, case studies, and behind-the-scenes editorial insight.',
                'body' => "The journal mixes practical guidance with story-driven recaps. It is where we document process, share useful planning frameworks, and show how a polished visual narrative is built.",
                'featured_media_uuid' => '8c2b7ca2-2213-49d6-8a02-3272efb3c4fb',
                'is_system' => 0,
            ],
            [
                'title' => 'Testimonials',
                'slug' => 'testimonials',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'Thoughtful feedback from couples, founders, and portrait clients.',
                'body' => "We care as much about the experience around the images as the photographs themselves. These notes reflect what clients felt during the process, not just after the gallery arrived.",
                'featured_media_uuid' => 'd7a1a1d7-eb76-4b7d-b76c-36c8fdb7b54d',
                'is_system' => 0,
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'template' => 'default',
                'status' => 'published',
                'excerpt' => 'Share your date, direction, or campaign idea and we will map the next step.',
                'body' => "Tell us the feeling you want your work to carry. Date, location, scale, and mood are all useful starting points. If your ideas are still forming, that is fine too.",
                'featured_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'is_system' => 0,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'template' => 'legal',
                'status' => 'published',
                'excerpt' => 'How Mesh Photography handles inquiries, bookings, media, and communication data.',
                'body' => "We only collect the information needed to answer inquiries, manage bookings, and deliver services. Inquiry details are stored securely, used for direct communication about your project, and never sold to third parties.\n\nIf you would like us to update or remove your contact information, email hello@meshphotography.com.",
                'featured_media_uuid' => null,
                'is_system' => 1,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function homeSections(): array
    {
        return [
            [
                'section_key' => 'hero',
                'section_type' => 'hero',
                'title' => 'Stories with atmosphere, restraint, and clarity.',
                'subtitle' => 'Mesh Photography',
                'body' => 'Editorial wedding coverage, guided portraits, and brand campaigns built with a calm production rhythm.',
                'cta_label' => 'Start Your Inquiry',
                'cta_url' => '/contact',
                'media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'json_payload' => [
                    'eyebrow' => 'Washington, DC and destination commissions',
                    'supporting_copy' => 'Premium photography for couples, founders, and teams who care about visual continuity.',
                ],
                'sort_order' => 1,
            ],
            [
                'section_key' => 'brand-intro',
                'section_type' => 'brand-intro',
                'title' => 'A studio built for refined pacing.',
                'subtitle' => 'Process',
                'body' => 'We shape coverage around light, movement, and the emotional tempo of the day so the work feels polished without feeling staged.',
                'media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'json_payload' => [
                    'stats' => [
                        ['label' => 'Weddings documented', 'value' => '120+'],
                        ['label' => 'Portrait commissions', 'value' => '300+'],
                        ['label' => 'Brand campaigns', 'value' => '45+'],
                    ],
                ],
                'sort_order' => 2,
            ],
            [
                'section_key' => 'featured-galleries',
                'section_type' => 'featured-galleries',
                'title' => 'Featured galleries',
                'subtitle' => 'Selected stories',
                'body' => 'A mix of celebration, portrait, and brand work that shows how a full visual narrative holds together.',
                'json_payload' => [
                    'limit' => 3,
                ],
                'sort_order' => 3,
            ],
            [
                'section_key' => 'services-teaser',
                'section_type' => 'services-teaser',
                'title' => 'Signature services',
                'subtitle' => 'Coverage built to fit the story',
                'body' => 'From full wedding weekends to portrait commissions and brand campaigns, each service starts with thoughtful planning.',
                'json_payload' => [
                    'limit' => 3,
                ],
                'sort_order' => 4,
            ],
            [
                'section_key' => 'testimonials-strip',
                'section_type' => 'testimonials-strip',
                'title' => 'What clients remember most',
                'subtitle' => 'Experience',
                'body' => 'Calm direction, consistent communication, and images that still feel alive months later.',
                'json_payload' => [
                    'limit' => 3,
                ],
                'sort_order' => 5,
            ],
            [
                'section_key' => 'featured-blog-posts',
                'section_type' => 'featured-blog-posts',
                'title' => 'From the journal',
                'subtitle' => 'Planning and perspective',
                'body' => 'Useful guidance for couples, portrait clients, and brand teams preparing for a polished shoot.',
                'json_payload' => [
                    'limit' => 3,
                ],
                'sort_order' => 6,
            ],
            [
                'section_key' => 'cta-banner',
                'section_type' => 'cta-banner',
                'title' => 'Ready to shape the story well from the start?',
                'subtitle' => 'Inquiry',
                'body' => 'Share your date, location, or campaign direction and we will recommend the best next step.',
                'cta_label' => 'Contact the Studio',
                'cta_url' => '/contact',
                'sort_order' => 7,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function aboutSections(): array
    {
        return [
            [
                'section_key' => 'about-intro',
                'section_type' => 'intro',
                'title' => 'A studio built around calm direction and visual continuity.',
                'subtitle' => 'About the Studio',
                'body' => "Mesh Photography approaches every commission with the same goal: create work that feels polished, emotionally true, and easy to live inside for years.\n\nWe blend editorial composition with guided, human direction so the process stays clear without becoming performative.",
                'sort_order' => 1,
            ],
            [
                'section_key' => 'about-approach',
                'section_type' => 'split-content',
                'title' => 'How we work',
                'subtitle' => 'Process',
                'body' => "Preparation shapes confidence. We start with pacing, light, and priorities so the creative work has structure before the camera ever comes out.\n\nDuring the shoot, we move with restraint: offering direction where it helps, then stepping back when the moment is already carrying its own energy.",
                'json_payload' => [
                    'side_title' => 'What clients feel',
                    'side_body' => "Clear communication before the shoot.\nCalm guidance during key moments.\nA visual result that feels cohesive instead of over-produced.\n\nThat balance is what lets the final gallery feel both elevated and believable.",
                ],
                'sort_order' => 2,
            ],
            [
                'section_key' => 'about-story',
                'section_type' => 'story-block',
                'title' => 'The work is shaped as much by atmosphere as by aesthetics.',
                'subtitle' => 'Philosophy',
                'body' => "We are interested in the full experience around the image: how a room felt before the ceremony, how a founder settled into confidence midway through a portrait session, how a brand space carried tone before a single frame was delivered.\n\nThat is why pacing, communication, and observation matter just as much as lighting or composition.",
                'sort_order' => 3,
            ],
            [
                'section_key' => 'about-faq',
                'section_type' => 'faq',
                'title' => 'A few things clients usually want to know',
                'subtitle' => 'FAQ',
                'json_payload' => [
                    'items' => [
                        [
                            'question' => 'What kinds of projects does the studio take on?',
                            'answer' => 'Wedding weekends, portrait commissions, and editorial brand projects are the core focus. Each is approached with the same emphasis on planning, atmosphere, and cohesive delivery.',
                        ],
                        [
                            'question' => 'Do you help with direction during the shoot?',
                            'answer' => 'Yes. Direction is part of the process, but it is used with restraint. The aim is to create confidence and shape without making the final images feel stiff or overly posed.',
                        ],
                        [
                            'question' => 'How early should we reach out?',
                            'answer' => 'The earlier the better, especially for weddings and campaign work. Early inquiries create more room for thoughtful planning, scheduling, and creative alignment.',
                        ],
                    ],
                ],
                'sort_order' => 4,
            ],
            [
                'section_key' => 'about-cta',
                'section_type' => 'cta-banner',
                'title' => 'If the tone feels right, the next step is simple.',
                'subtitle' => 'Start the Conversation',
                'body' => 'Share your date, project type, or campaign direction and we will recommend the clearest path forward.',
                'cta_label' => 'Inquire Now',
                'cta_url' => '/contact',
                'sort_order' => 5,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function contactBlocks(): array
    {
        return [
            [
                'name' => 'Contact Hero',
                'block_key' => 'contact-hero',
                'block_type' => 'content',
                'title' => 'Start the conversation with clarity from the first message.',
                'body' => 'Share the date, scope, or campaign direction and we will shape the most useful next step without unnecessary back and forth.',
                'json_payload' => [
                    'eyebrow' => 'Get in Touch',
                ],
            ],
            [
                'name' => 'Contact Process',
                'block_key' => 'contact-process',
                'block_type' => 'content',
                'title' => 'From first message to confirmed booking',
                'body' => 'The process is designed to feel calm, clear, and appropriately paced from inquiry through scheduling.',
                'json_payload' => [
                    'eyebrow' => 'How It Works',
                    'steps' => [
                        [
                            'number' => '1',
                            'title' => 'Share Your Vision',
                            'body' => 'Use the inquiry form to outline the project type, location, timing, and anything already taking shape.',
                        ],
                        [
                            'number' => '2',
                            'title' => 'Review & Reply',
                            'body' => 'We review the goals, confirm availability, and send a clear recommendation based on scope and priorities.',
                        ],
                        [
                            'number' => '3',
                            'title' => 'Plan & Create',
                            'body' => 'Once aligned, the project moves into scheduling, preparation, and a production rhythm built around the story.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Contact Form Intro',
                'block_key' => 'contact-form-intro',
                'block_type' => 'content',
                'title' => 'Helpful context for a stronger first reply',
                'body' => "A few specific details make the response more useful right away.\n\nIf you already know the date, venue, team size, or intended feeling of the work, include it here. If you are still refining the idea, that is completely fine too.",
            ],
            [
                'name' => 'Contact Booking CTA',
                'block_key' => 'contact-booking-cta',
                'block_type' => 'cta',
                'title' => 'Prefer a structured availability request instead?',
                'body' => 'If the date and scope are already clear, move straight into a booking request and we will pick up from there.',
                'json_payload' => [
                    'button_label' => 'Request Availability',
                    'button_url' => '/booking',
                ],
            ],
            [
                'name' => 'Inquiry Promise',
                'block_key' => 'inquiry-promise',
                'block_type' => 'content',
                'title' => 'What happens after you inquire',
                'body' => 'Your message is reviewed carefully, stored securely, and answered with a thoughtful recommendation rather than a generic auto-response.',
                'json_payload' => [
                    'response_window' => 'Within 2 business days',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reusableBlocks(): array
    {
        return [
            [
                'name' => 'Global CTA',
                'block_key' => 'global-cta',
                'block_type' => 'cta',
                'title' => 'Tell us what you are planning.',
                'body' => 'We will shape the right coverage once we understand the mood, scale, and setting.',
                'json_payload' => [
                    'button_label' => 'Inquire Now',
                    'button_url' => '/contact',
                ],
            ],
            [
                'name' => 'Footer Note',
                'block_key' => 'footer-note',
                'block_type' => 'footer',
                'title' => 'Mesh Photography',
                'body' => 'Editorial imagery for weddings, portraits, and considered brands.',
                'json_payload' => [
                    'location' => 'Washington, DC and destination',
                ],
            ],
            [
                'name' => 'Inquiry Promise',
                'block_key' => 'inquiry-promise',
                'block_type' => 'content',
                'title' => 'What happens after you inquire',
                'body' => 'You can expect a thoughtful response, a clear recommendation, and next steps that match your timeline.',
                'json_payload' => [
                    'response_window' => 'Within 2 business days',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function services(): array
    {
        return [
            [
                'title' => 'Wedding Weekend Coverage',
                'slug' => 'wedding-weekend-coverage',
                'short_description' => 'Full-day wedding coverage with editorial portraits and quiet documentary observation.',
                'full_description' => "Designed for couples who want atmosphere and continuity, this collection covers the full shape of the celebration from preparation through reception. We balance direction with observation so portraits feel intentional and the unscripted moments still land with emotional weight.",
                'cover_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'sort_order' => 1,
                'featured' => 1,
                'status' => 'published',
                'price_display' => 'Collections from $6,800',
            ],
            [
                'title' => 'Portrait Direction',
                'slug' => 'portrait-direction',
                'short_description' => 'Guided portrait sessions for personal milestones, editorial profiles, and creative commissions.',
                'full_description' => "Portrait sessions are paced around comfort, movement, and visual shape. We handle location planning, styling references, and direction that keeps the work polished without flattening personality.",
                'cover_media_uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'sort_order' => 2,
                'featured' => 1,
                'status' => 'published',
                'price_display' => 'Sessions from $1,250',
            ],
            [
                'title' => 'Brand Editorials',
                'slug' => 'brand-editorials',
                'short_description' => 'Narrative-first brand photography for founders, hospitality, product, and campaign launches.',
                'full_description' => "We build brand shoots around the feeling a client wants their audience to receive. The result is a library of images that can flex across launch campaigns, web experiences, press features, and social rollout.",
                'cover_media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'sort_order' => 3,
                'featured' => 1,
                'status' => 'published',
                'price_display' => 'Projects from $2,900',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function galleryCategories(): array
    {
        return [
            [
                'name' => 'Weddings',
                'slug' => 'weddings',
                'description' => 'Celebrations, ceremonies, and full wedding stories.',
                'sort_order' => 1,
                'status' => 'published',
            ],
            [
                'name' => 'Portraits',
                'slug' => 'portraits',
                'description' => 'Directed portrait work for couples, families, and personal brands.',
                'sort_order' => 2,
                'status' => 'published',
            ],
            [
                'name' => 'Brands',
                'slug' => 'brands',
                'description' => 'Editorial storytelling for hospitality, retail, and founder-led businesses.',
                'sort_order' => 3,
                'status' => 'published',
            ],
            [
                'name' => 'Editorial',
                'slug' => 'editorial',
                'description' => 'Narrative studies with a magazine-minded point of view.',
                'sort_order' => 4,
                'status' => 'published',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function galleries(): array
    {
        return [
            [
                'title' => 'Downtown Afterglow',
                'slug' => 'downtown-afterglow',
                'excerpt' => 'A city wedding with layered portraits, candlelight, and a fast-moving dance floor.',
                'story_intro' => 'This gallery moves from quiet getting-ready frames into a reception full of energy, keeping the visual language consistent from first look through final dance set.',
                'primary_category_slug' => 'weddings',
                'category_slugs' => ['weddings', 'editorial'],
                'cover_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'media_uuids' => [
                    '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                    '293db6c0-6ae3-44a3-9e4e-4ea1d5f3c492',
                    'd3bfdbb4-c919-4354-af76-ad6515e5f3df',
                ],
                'featured' => 1,
                'status' => 'published',
                'location' => 'Washington, DC',
                'event_date' => '2025-09-21',
                'client_name' => 'Elena and Marcus',
                'sort_order' => 1,
                'published_at' => '2025-10-06 09:00:00',
                'created_by' => 'ava.stone@mesh.local',
                'updated_by' => 'nolan.reyes@mesh.local',
            ],
            [
                'title' => 'Coastline Vows',
                'slug' => 'coastline-vows',
                'excerpt' => 'Wind, open horizon, and a restrained color palette on the coast.',
                'story_intro' => 'A destination celebration photographed with an emphasis on weather, space, and the way movement changes across the day.',
                'primary_category_slug' => 'weddings',
                'category_slugs' => ['weddings'],
                'cover_media_uuid' => '4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8',
                'media_uuids' => [
                    '4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8',
                    '293db6c0-6ae3-44a3-9e4e-4ea1d5f3c492',
                ],
                'featured' => 1,
                'status' => 'published',
                'location' => 'Big Sur, California',
                'event_date' => '2025-07-14',
                'client_name' => 'Clara and Jonah',
                'sort_order' => 2,
                'published_at' => '2025-08-01 10:30:00',
                'created_by' => 'ava.stone@mesh.local',
                'updated_by' => 'ava.stone@mesh.local',
            ],
            [
                'title' => 'Atelier Portrait Study',
                'slug' => 'atelier-portrait-study',
                'excerpt' => 'A portrait commission built around restraint, texture, and directional light.',
                'story_intro' => 'This session used one studio, soft tonal styling, and a narrow direction brief to create a portrait series that feels calm and precise.',
                'primary_category_slug' => 'portraits',
                'category_slugs' => ['portraits', 'editorial'],
                'cover_media_uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'media_uuids' => [
                    '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                    'd7a1a1d7-eb76-4b7d-b76c-36c8fdb7b54d',
                ],
                'featured' => 1,
                'status' => 'published',
                'location' => 'Alexandria Studio',
                'event_date' => '2025-05-02',
                'client_name' => 'Sophia Bennett',
                'sort_order' => 3,
                'published_at' => '2025-05-12 08:00:00',
                'created_by' => 'nolan.reyes@mesh.local',
                'updated_by' => 'nolan.reyes@mesh.local',
            ],
            [
                'title' => 'Maison Brand Story',
                'slug' => 'maison-brand-story',
                'excerpt' => 'An editorial brand campaign for a founder-led product line.',
                'story_intro' => 'The goal was to create a brand image library that could move between web, press, and launch materials without losing coherence.',
                'primary_category_slug' => 'brands',
                'category_slugs' => ['brands', 'editorial'],
                'cover_media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'media_uuids' => [
                    'ec8fe29d-15a0-4745-86ea-6262367503ce',
                    '8c2b7ca2-2213-49d6-8a02-3272efb3c4fb',
                ],
                'featured' => 0,
                'status' => 'published',
                'location' => 'Georgetown Atelier',
                'event_date' => '2025-03-18',
                'client_name' => 'Maison Rue',
                'sort_order' => 4,
                'published_at' => '2025-03-28 11:00:00',
                'created_by' => 'mia.carter@mesh.local',
                'updated_by' => 'mia.carter@mesh.local',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function testimonials(): array
    {
        return [
            [
                'client_name' => 'Elena and Marcus',
                'client_label' => 'Wedding clients',
                'quote' => 'The entire experience felt calm, intentional, and deeply personal. The gallery looked exactly how the weekend felt.',
                'long_form_story' => 'What stood out most was how steady the process felt. We were never rushed, never over-directed, and still ended up with photographs that feel polished and cinematic. The images hold both the elegance and the energy of the day.',
                'rating' => 5,
                'featured' => 1,
                'service_slug' => 'wedding-weekend-coverage',
                'gallery_slug' => 'downtown-afterglow',
                'portrait_media_uuid' => '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10',
                'event_date' => '2025-09-21',
                'location' => 'Washington, DC',
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'client_name' => 'Sophia Bennett',
                'client_label' => 'Portrait client',
                'quote' => 'I felt guided without ever feeling over-posed. The final images looked sophisticated and still felt like me.',
                'long_form_story' => 'From styling references to pacing in the studio, everything felt considered. The finished portraits gave me a mix of polished editorial frames and the softer, quieter moments I wanted for personal use.',
                'rating' => 5,
                'featured' => 1,
                'service_slug' => 'portrait-direction',
                'gallery_slug' => 'atelier-portrait-study',
                'portrait_media_uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'event_date' => '2025-05-02',
                'location' => 'Alexandria, Virginia',
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'client_name' => 'Tessa Moore',
                'client_label' => 'Founder, Maison Rue',
                'quote' => 'We needed a brand library that felt elevated and usable. Mesh delivered both.',
                'long_form_story' => 'The planning process translated our brand values into something visual very quickly. The images have range, but they still feel like part of the same story. They now anchor our website, launch deck, and seasonal campaigns.',
                'rating' => 5,
                'featured' => 1,
                'service_slug' => 'brand-editorials',
                'gallery_slug' => 'maison-brand-story',
                'portrait_media_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'event_date' => '2025-03-18',
                'location' => 'Washington, DC',
                'status' => 'published',
                'sort_order' => 3,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function blogCategories(): array
    {
        return [
            [
                'name' => 'Planning',
                'slug' => 'planning',
                'description' => 'Useful guidance for preparing weddings, portrait sessions, and production timelines.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Behind the Scenes',
                'slug' => 'behind-the-scenes',
                'description' => 'Notes on process, prep, workflow, and production rhythm.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Brand Storytelling',
                'slug' => 'brand-storytelling',
                'description' => 'Editorial strategy and photography thinking for founder-led brands.',
                'sort_order' => 3,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function blogTags(): array
    {
        return [
            ['name' => 'Timeline', 'slug' => 'timeline', 'description' => 'Timing and production planning insights.'],
            ['name' => 'Lighting', 'slug' => 'lighting', 'description' => 'How light shapes the atmosphere of a session or event.'],
            ['name' => 'Locations', 'slug' => 'locations', 'description' => 'Venue, travel, and site-scouting ideas.'],
            ['name' => 'Wardrobe', 'slug' => 'wardrobe', 'description' => 'Styling guidance for portrait and campaign sessions.'],
            ['name' => 'Editorial', 'slug' => 'editorial', 'description' => 'Magazine-minded pacing, framing, and storytelling.'],
            ['name' => 'Production', 'slug' => 'production', 'description' => 'Brand shoot planning and logistics.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function blogPosts(): array
    {
        return [
            [
                'author_email' => 'nolan.reyes@mesh.local',
                'title' => 'How to Build a Wedding Day Timeline That Still Leaves Room to Breathe',
                'slug' => 'wedding-day-timeline-that-still-leaves-room-to-breathe',
                'excerpt' => 'A practical structure for couples who want polished coverage without turning the day into a checklist.',
                'body_long' => "A strong wedding timeline does not just protect logistics. It protects how the day feels. When a schedule is too compressed, portraits become rushed, transitions get noisy, and even beautiful details lose breathing room.\n\nWe typically recommend building the day around three anchor moments: unhurried preparation, a protected portrait window, and a transition buffer before reception events begin. Those anchor points allow everything else to flex without the entire day collapsing into urgency.\n\nThe best timelines make room for emotional pace as much as practical movement. That usually means adding margin before travel, reducing the number of hard location changes, and being realistic about how long family formals take when people are actually present and engaged.",
                'featured_image_uuid' => 'd3bfdbb4-c919-4354-af76-ad6515e5f3df',
                'cover_gallery_slug' => 'downtown-afterglow',
                'status' => 'published',
                'visibility' => 'public',
                'is_featured' => 1,
                'allow_comments' => 0,
                'published_at' => '2025-10-12 09:15:00',
                'scheduled_at' => null,
                'archived_at' => null,
                'reading_time' => 5,
                'meta_summary' => 'Planning advice for a wedding schedule that protects atmosphere, portraits, and emotional pacing.',
                'canonical_url' => null,
                'view_count' => 184,
                'category_slugs' => ['planning'],
                'tag_slugs' => ['timeline', 'locations', 'editorial'],
                'media_uuids' => ['d3bfdbb4-c919-4354-af76-ad6515e5f3df', '0f6a4f0c-d2c0-4de7-9d00-7c5c58f44a10'],
            ],
            [
                'author_email' => 'nolan.reyes@mesh.local',
                'title' => 'Why Quiet Portrait Direction Produces Stronger Editorial Frames',
                'slug' => 'why-quiet-portrait-direction-produces-stronger-editorial-frames',
                'excerpt' => 'Less noise, clearer pacing, and more believable movement almost always lead to better portrait work.',
                'body_long' => "Editorial portraits rarely come from constant instruction. They come from a small number of precise cues delivered at the right moment. When a client is given too many corrections, posture tightens and expression starts to look managed instead of lived.\n\nQuiet direction works because it creates enough structure for confidence while leaving room for a person's natural cadence to show up. We usually begin with posture and light, then let movement do the rest.\n\nThat approach is especially helpful for clients who are not used to being photographed. It reduces performance pressure and allows the session to feel collaborative instead of overly produced.",
                'featured_image_uuid' => '31b0734d-e0b6-453f-891b-4a59fd8d6f12',
                'cover_gallery_slug' => 'atelier-portrait-study',
                'status' => 'published',
                'visibility' => 'public',
                'is_featured' => 1,
                'allow_comments' => 0,
                'published_at' => '2025-06-01 08:30:00',
                'scheduled_at' => null,
                'archived_at' => null,
                'reading_time' => 4,
                'meta_summary' => 'An explanation of how restrained direction leads to stronger portrait photographs.',
                'canonical_url' => null,
                'view_count' => 132,
                'category_slugs' => ['behind-the-scenes'],
                'tag_slugs' => ['lighting', 'wardrobe', 'editorial'],
                'media_uuids' => ['31b0734d-e0b6-453f-891b-4a59fd8d6f12'],
            ],
            [
                'author_email' => 'mia.carter@mesh.local',
                'title' => 'What Brand Teams Actually Need From an Editorial Photo Day',
                'slug' => 'what-brand-teams-actually-need-from-an-editorial-photo-day',
                'excerpt' => 'A useful way to plan brand photography around launch needs, channel usage, and narrative consistency.',
                'body_long' => "Brand shoots often fail before the first frame because the brief is too visual and not strategic enough. A useful shot list should connect directly to where the work will be used: homepage hero blocks, launch emails, press features, product pages, and social rollout all demand different crops and narrative weight.\n\nThe most effective brand sessions combine three image categories: anchor frames that carry the full visual tone, utility frames that support everyday publishing needs, and atmospheric details that give the brand texture.\n\nIf those categories are planned in advance, the final gallery becomes an actual content system rather than a folder of pretty but disconnected photos.",
                'featured_image_uuid' => 'ec8fe29d-15a0-4745-86ea-6262367503ce',
                'cover_gallery_slug' => 'maison-brand-story',
                'status' => 'published',
                'visibility' => 'public',
                'is_featured' => 1,
                'allow_comments' => 0,
                'published_at' => '2025-04-14 10:00:00',
                'scheduled_at' => null,
                'archived_at' => null,
                'reading_time' => 6,
                'meta_summary' => 'A planning framework for brand photography that produces useful, consistent assets.',
                'canonical_url' => null,
                'view_count' => 96,
                'category_slugs' => ['brand-storytelling', 'planning'],
                'tag_slugs' => ['production', 'editorial'],
                'media_uuids' => ['ec8fe29d-15a0-4745-86ea-6262367503ce', '8c2b7ca2-2213-49d6-8a02-3272efb3c4fb'],
            ],
            [
                'author_email' => 'mia.carter@mesh.local',
                'title' => 'A Behind-the-Scenes Look at Preparing for a Destination Wedding Weekend',
                'slug' => 'behind-the-scenes-preparing-for-a-destination-wedding-weekend',
                'excerpt' => 'How we plan coverage, scout conditions, and protect consistency across a travel-heavy event weekend.',
                'body_long' => "Destination work adds visual opportunity, but it also increases the number of variables that can change quickly. Travel timing, location accessibility, weather movement, and local vendor coordination all affect how smoothly coverage runs.\n\nBefore a destination wedding weekend, we build a simplified location plan, identify fallback portrait options, and map how light changes across the core environments. That preparation matters because once the event begins, decisions need to happen quietly and fast.\n\nThe goal is not over-planning. It is removing preventable friction so the creative attention stays where it belongs: on the couple and the story unfolding in front of us.",
                'featured_image_uuid' => '4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8',
                'cover_gallery_slug' => 'coastline-vows',
                'status' => 'published',
                'visibility' => 'public',
                'is_featured' => 0,
                'allow_comments' => 0,
                'published_at' => '2025-08-18 07:45:00',
                'scheduled_at' => null,
                'archived_at' => null,
                'reading_time' => 5,
                'meta_summary' => 'Production notes for preparing destination wedding photography with calm and consistency.',
                'canonical_url' => null,
                'view_count' => 74,
                'category_slugs' => ['behind-the-scenes', 'planning'],
                'tag_slugs' => ['timeline', 'locations', 'production'],
                'media_uuids' => ['4e78a6e5-30d7-4a29-b9d3-df8aaf57c3d8'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function inquiries(): array
    {
        return [
            [
                'first_name' => 'Naomi',
                'last_name' => 'Foster',
                'email' => 'naomi.foster@example.com',
                'phone' => '+1-202-555-0130',
                'company_name' => null,
                'service_interest' => 'Wedding Weekend Coverage',
                'preferred_date' => '2026-05-16',
                'budget_range' => '$6,000-$8,000',
                'location' => 'Charleston, South Carolina',
                'referral_source' => 'Instagram',
                'message' => 'We are planning a full weekend celebration with around 110 guests and want the photography to feel polished but still emotionally open. We love your use of movement and would like to know what collection fits best.',
                'status' => 'responded',
                'source_ip' => '127.0.0.1',
                'user_agent' => 'Seeder/DemoContent',
                'notes' => [
                    'Sent pricing guide and scheduled a consult for next Tuesday.',
                ],
            ],
            [
                'first_name' => 'Aaron',
                'last_name' => 'Price',
                'email' => 'aaron.price@example.com',
                'phone' => '+1-202-555-0151',
                'company_name' => 'North House Design',
                'service_interest' => 'Brand Editorials',
                'preferred_date' => '2026-02-11',
                'budget_range' => '$3,000-$5,000',
                'location' => 'Washington, DC',
                'referral_source' => 'Referral',
                'message' => 'We are refreshing our studio website and need a brand library that covers portraits, process, and styled product moments. We launch in March and need assets that feel elevated and warm.',
                'status' => 'in_progress',
                'source_ip' => '127.0.0.1',
                'user_agent' => 'Seeder/DemoContent',
                'notes' => [
                    'Requested brand deck and site map to refine shot list.',
                ],
            ],
            [
                'first_name' => 'Priya',
                'last_name' => 'Shah',
                'email' => 'priya.shah@example.com',
                'phone' => '+1-202-555-0168',
                'company_name' => null,
                'service_interest' => 'Portrait Direction',
                'preferred_date' => '2026-01-24',
                'budget_range' => '$1,000-$1,500',
                'location' => 'Alexandria, Virginia',
                'referral_source' => 'Google',
                'message' => 'I need new portraits for a speaking profile and publication features. I want something polished and modern without feeling too corporate.',
                'status' => 'new',
                'source_ip' => '127.0.0.1',
                'user_agent' => 'Seeder/DemoContent',
                'notes' => [
                    'Awaiting confirmation on wardrobe consult availability.',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function bookingRequests(): array
    {
        return [
            [
                'inquiry_email' => 'naomi.foster@example.com',
                'service_slug' => 'wedding-weekend-coverage',
                'first_name' => 'Naomi',
                'last_name' => 'Foster',
                'email' => 'naomi.foster@example.com',
                'phone' => '+1-202-555-0130',
                'requested_date' => '2026-05-16',
                'requested_time' => '15:30:00',
                'event_type' => 'Wedding weekend',
                'location' => 'Charleston, South Carolina',
                'hours_needed' => 10.50,
                'guest_count' => 110,
                'notes' => 'Likely includes rehearsal dinner coverage on the evening before.',
                'status' => 'quoted',
                'source_ip' => '127.0.0.1',
                'user_agent' => 'Seeder/DemoContent',
            ],
            [
                'inquiry_email' => 'aaron.price@example.com',
                'service_slug' => 'brand-editorials',
                'first_name' => 'Aaron',
                'last_name' => 'Price',
                'email' => 'aaron.price@example.com',
                'phone' => '+1-202-555-0151',
                'requested_date' => '2026-02-11',
                'requested_time' => '09:00:00',
                'event_type' => 'Brand campaign shoot',
                'location' => 'Washington, DC',
                'hours_needed' => 6.00,
                'guest_count' => 6,
                'notes' => 'Shot list to include founder portraits, studio process, and styled product detail coverage.',
                'status' => 'in_progress',
                'source_ip' => '127.0.0.1',
                'user_agent' => 'Seeder/DemoContent',
            ],
        ];
    }
}
