<?php

declare(strict_types=1);

return [
    /*
     * Content-Security-Policy
     * Adjust allowed sources to match CDN/font/analytics used in production.
     */
    'csp' => [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline'",   // unsafe-inline needed for Vite inline scripts
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "object-src 'none'",
        "upgrade-insecure-requests",
    ],

    'x_frame_options'        => 'DENY',
    'x_content_type_options' => 'nosniff',
    'referrer_policy'        => 'strict-origin-when-cross-origin',
    'permissions_policy'     => 'camera=(), microphone=(), geolocation=()',

    /*
     * HSTS — only emitted when APP_ENV=production.
     * max-age=31536000 = 1 year; includeSubDomains covers admin subdomain if any.
     */
    'hsts_max_age'            => 31536000,
    'hsts_include_subdomains' => true,
    'hsts_preload'            => true,
];
