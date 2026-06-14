# Mesh Photography — Launch Checklist

Complete every item before going live. Check each box as you go.

---

## Pre-deploy

- [ ] All migrations run: `php database/console.php migrate`
- [ ] Production database seeded: `php database/console.php seed:production`
- [ ] `.env` created from `.env.production.example` — all placeholders replaced
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true` verified in `.env`
- [ ] `JWT_SECRET` is a unique 64-char hex string (never reused from dev)
- [ ] `CORS_ALLOWED_ORIGINS` set to production domain only

---

## Server setup

- [ ] PHP 8.2+ installed with extensions: pdo_mysql, gd (or imagick), mbstring, json, openssl, curl
- [ ] PHP OPcache enabled (`opcache.enable=1`) — see `deploy/php.ini.production`
- [ ] `upload_max_filesize=12M`, `post_max_size=13M` in production `php.ini`
- [ ] `backend/public/uploads/` writable by web server (`chown www-data`, `chmod 755`)
- [ ] `backend/storage/logs/` writable by web server
- [ ] Apache modules enabled: `a2enmod rewrite ssl headers`
- [ ] VirtualHost installed from `deploy/apache/vhost.conf`
- [ ] `a2ensite meshphoto && systemctl reload apache2`
- [ ] SSL certificate issued via Let's Encrypt: `certbot --apache -d meshphoto.com -d www.meshphoto.com`

---

## DNS & SSL (PL-17)

- [ ] A record for `meshphoto.com` → server IP
- [ ] A record for `www.meshphoto.com` → server IP (redirect to non-www via vhost)
- [ ] DNS propagated globally (use `dig meshphoto.com +short` or dnschecker.org)
- [ ] HTTPS accessible at `https://meshphoto.com`
- [ ] HTTP→HTTPS redirect working (301)
- [ ] `www`→non-www redirect working (301)
- [ ] SSL Labs score A or better: https://ssllabs.com/ssltest/

---

## Frontend build

- [ ] `npm ci --prefix frontend`
- [ ] `npm run build --prefix frontend` — no TypeScript errors
- [ ] `frontend/dist/` deployed to `/var/www/meshphoto/frontend/dist/`
- [ ] React app loads at `https://meshphoto.com`
- [ ] All public routes navigate correctly (portfolio, blog, services, contact, booking)

---

## Lighthouse audit (PL-7)

Run against production URL after full deploy:

```bash
npx lighthouse https://meshphoto.com --output html --output-path ./deploy/lighthouse-report.html
```

Targets:
- [ ] Performance ≥ 85 (mobile)
- [ ] Accessibility ≥ 90
- [ ] Best Practices ≥ 90
- [ ] SEO ≥ 90

---

## WCAG 2.1 AA (PL-9)

- [ ] Install axe DevTools browser extension
- [ ] Run axe on: `/`, `/portfolio`, `/blog`, `/contact`, `/booking`, `/admin/login`
- [ ] Zero critical or serious violations on each page
- [ ] All images have meaningful alt text (or `alt=""` for decorative)
- [ ] All form inputs have associated labels
- [ ] Focus indicators visible on all interactive elements
- [ ] Colour contrast ratio ≥ 4.5:1 for body text

---

## Google Search Console (PL-8)

- [ ] Property added for `https://meshphoto.com`
- [ ] Domain ownership verified (DNS TXT record or HTML file)
- [ ] Sitemap submitted: `https://meshphoto.com/api/v1/sitemap`
- [ ] URL inspection on homepage — no crawl errors
- [ ] `robots.txt` accessible at `https://meshphoto.com/robots.txt`

---

## Email flows (PL-15)

- [ ] Contact form submits → admin receives notification email
- [ ] Contact form submits → visitor receives confirmation email
- [ ] Booking form submits → admin receives notification email
- [ ] Booking form submits → visitor receives confirmation email
- [ ] Password reset email delivers and link works
- [ ] All emails render correctly in Gmail and Outlook (use Litmus or Email on Acid)
- [ ] SPF and DKIM records set for sending domain

---

## File upload verification (PL-16)

- [ ] Upload a JPEG via admin media library → file appears in grid
- [ ] Upload a PNG → thumbnail generated
- [ ] Upload a file > 10 MB → rejected with correct error message
- [ ] Upload a PHP file → rejected (MIME/extension block)
- [ ] `backend/public/uploads/` directory not listable (403 on direct dir access)

---

## Uptime monitoring (PL-12)

Set up UptimeRobot (free) or Pingdom to monitor:
- [ ] `https://meshphoto.com/api/v1/health` — HTTP 200, keyword `"ok":true`
- [ ] `https://meshphoto.com` — HTTP 200
- [ ] Alert email configured to `admin@meshphoto.com`
- [ ] Check interval: every 5 minutes

---

## Backup & log rotation (PL-11, PL-13)

- [ ] `deploy/scripts/backup-mysql.sh` deployed to `/var/www/meshphoto/deploy/scripts/`
- [ ] Script is executable: `chmod +x backup-mysql.sh`
- [ ] Cron entry added (run as `www-data` or deploy user):
  ```
  0 2 * * * /var/www/meshphoto/deploy/scripts/backup-mysql.sh >> /var/log/meshphoto-backup.log 2>&1
  ```
- [ ] Test backup manually: `bash /var/www/meshphoto/deploy/scripts/backup-mysql.sh`
- [ ] Backup file created in `/var/backups/meshphoto/`
- [ ] Log rotation config installed: `cp deploy/logrotate/mesh-photo /etc/logrotate.d/`
- [ ] Test rotation: `logrotate -d /etc/logrotate.d/mesh-photo`

---

## Load test (PL-10)

Run after staging environment is up, before final DNS cut-over:

```bash
k6 run deploy/scripts/load-test.js --env BASE_URL=https://staging.meshphoto.com
```

- [ ] Error rate < 1%
- [ ] p95 response time < 500ms at 100 concurrent users
- [ ] No 5xx errors under load

---

## Post-launch monitoring (PL-19)

Watch for the first 48 hours:
- [ ] `tail -f /var/www/meshphoto/backend/storage/logs/$(date +%Y-%m-%d).log`
- [ ] Apache error log: `tail -f /var/log/apache2/meshphoto-error.log`
- [ ] PHP error log: `tail -f /var/log/php*-fpm.log` (if using FPM)
- [ ] No 500 errors in first 100 real requests
- [ ] Uptime monitor shows green
- [ ] Google Search Console — no crawl errors after 24h

---

## Sign-off

| Item | Checked by | Date |
|---|---|---|
| Server + SSL | | |
| Frontend build | | |
| Lighthouse ≥ 85 mobile | | |
| WCAG AA | | |
| Email flows | | |
| File uploads | | |
| Backup cron | | |
| Uptime monitoring | | |
| Load test passed | | |
| 48h post-launch review | | |
