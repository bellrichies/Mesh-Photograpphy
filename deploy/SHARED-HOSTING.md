# Shared Hosting Deployment Guide

Domain: `mesh-photo.bundly.ng`

This project should be deployed with `backend/public` as the domain document root. The React production build is copied into that folder, while PHP source, `.env`, Composer files, and storage stay outside the web-accessible directory.

## Required Server Settings

- PHP 8.2 or newer.
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`, `fileinfo`, and `gd`.
- Apache `mod_rewrite` enabled.
- MySQL or MariaDB with InnoDB.
- SSL enabled for `https://mesh-photo.bundly.ng`.

## Build A Clean Upload Package On Windows

From the project root:

```powershell
powershell -ExecutionPolicy Bypass -File deploy\scripts\build-shared-host-package.ps1
```

The script creates:

- `deploy/build/mesh-photo-shared-host/`
- `deploy/build/mesh-photo-shared-host.zip`

Upload and extract the ZIP into a non-public folder on the hosting account, for example:

```text
/home/CPANEL_USER/mesh-photo
```

After extraction, the backend should be here:

```text
/home/CPANEL_USER/mesh-photo/backend
```

## cPanel Domain Setup

In cPanel, create or edit the subdomain/addon domain:

```text
Domain: mesh-photo.bundly.ng
Document Root: /home/CPANEL_USER/mesh-photo/backend/public
```

Do not use the project root or `backend` folder as the document root. Only `backend/public` should be public.

## Database Setup

In cPanel MySQL Databases:

1. Create a database, for example `CPANEL_USER_meshphoto`.
2. Create a database user with a strong password.
3. Add the user to the database with all privileges.

## Environment File

On the server:

```bash
cd ~/mesh-photo/backend
cp .env.production.example .env
```

Edit `.env` and set:

```dotenv
APP_URL=https://mesh-photo.bundly.ng
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=CPANEL_USER_meshphoto
DB_USERNAME=CPANEL_USER_meshuser
DB_PASSWORD=your_real_database_password
CORS_ALLOWED_ORIGINS=https://mesh-photo.bundly.ng
SESSION_SECURE=true
ADMIN_EMAIL=your_admin_email
ADMIN_INITIAL_PASSWORD=your_temporary_strong_password
```

Generate `JWT_SECRET`:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Paste the generated 64-character value into `.env`.

## Install And Initialize

If your host has Terminal/SSH:

```bash
cd ~/mesh-photo/backend
composer install --no-dev --prefer-dist --optimize-autoloader
php database/console.php migrate
php database/console.php seed:production
```

If your host does not have SSH, run the package script locally first so `vendor/` is included, then ask the host to run the two PHP commands above or use cPanel Terminal if available.

## Permissions

Set these folders writable by PHP:

```text
backend/storage
backend/storage/logs
backend/storage/sessions
backend/public/uploads
```

Typical cPanel permissions:

```bash
find ~/mesh-photo/backend/storage -type d -exec chmod 755 {} \;
find ~/mesh-photo/backend/public/uploads -type d -exec chmod 755 {} \;
find ~/mesh-photo/backend/storage -type f -exec chmod 644 {} \;
find ~/mesh-photo/backend/public/uploads -type f -exec chmod 644 {} \;
```

If PHP reports `Permission denied` while loading a file from `backend/vendor`,
reset the whole backend tree to standard shared-host permissions:

```bash
find ~/mesh-photo/backend -type d -exec chmod 755 {} \;
find ~/mesh-photo/backend -type f -exec chmod 644 {} \;
chmod 755 ~/mesh-photo/backend/storage ~/mesh-photo/backend/storage/logs ~/mesh-photo/backend/storage/sessions ~/mesh-photo/backend/public/uploads
```

On cPanel installs under `public_html`, replace `~/mesh-photo/backend` with the
actual backend path, for example:

```bash
/home/CPANEL_USER/public_html/mesh-photo.bundly.ng/backend
```

## Final Checks

Open these URLs:

```text
https://mesh-photo.bundly.ng
https://mesh-photo.bundly.ng/admin/login
https://mesh-photo.bundly.ng/api/v1/health
https://mesh-photo.bundly.ng/robots.txt
https://mesh-photo.bundly.ng/api/v1/sitemap
```

Expected health response contains:

```json
{"ok":true}
```

After first login, change the seeded admin password immediately.

## If You Are Migrating Existing Local Content

Fresh production deployment only creates the admin user and core CMS settings. To move existing local content:

1. Export your local MySQL database and import it into the cPanel database.
2. Upload the contents of `backend/public/uploads/` to the same folder on the server.
3. Keep `backend/public/uploads/.htaccess` on the server.
4. Run `php database/console.php migrate` after import so any missing migrations are applied.
