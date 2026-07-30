# Hydrox Portal

Hydrox Portal is a Laravel 12 operations system for Hydrox Facility Management. It manages website bookings, subcontractor onboarding and profiles, work logs, payments, notifications, and business settings.

## Booking Integration

The future hydrox.au WordPress booking plugin can submit JSON bookings to:

```text
POST /api/bookings
X-Booking-Token: your-private-token
Content-Type: application/json
```

Required fields are `customer_name`, `email`, `phone`, and `service`. Optional fields include `external_reference`, `preferred_date`, `preferred_time`, `address`, `suburb`, `postcode`, and `notes`.

Set a unique `HYDROX_BOOKING_TOKEN` in the server environment. Never expose this token in public browser JavaScript; requests should be sent from the WordPress server.

Set `HYDROX_ADMIN_PASSWORD` in the deployment environment before running `php artisan db:seed`. Keep this value out of source control.

## Requirements

- PHP 8.3 or newer
- Composer 2
- Node.js 20 or newer with npm
- MySQL 8 or MariaDB 10.6 or newer for production
- SQLite or MySQL for local development
- PHP extensions commonly required by Laravel: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, and GD/Imagick where image handling is needed

## Local Installation

1. Clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Copy the environment example and update local values:

```bash
cp .env.example .env
php artisan key:generate
```

5. Create or configure the database, then run migrations:

```bash
php artisan migrate
```

6. Create the public storage link:

```bash
php artisan storage:link
```

7. Start the local application:

```bash
composer run dev
```

For upload-heavy local testing, use:

```bash
composer run serve:uploads
```

## Environment Setup

Important environment values:

- `APP_NAME` controls the application name.
- `APP_ENV` should be `local` locally and `production` on hosting.
- `APP_DEBUG` should be `true` locally and `false` in production.
- `APP_URL` should match the final domain in production.
- `DB_*` values must point to the selected local or production database.
- `FILESYSTEM_DISK=public` is used for uploaded report files and photos.
- `COMPANY_*` values control report branding and contact details.

Never commit the real `.env` file. Keep production secrets only in the hosting control panel or server environment.

## Production Deployment

Recommended VentraIP cPanel deployment flow:

1. Create a MySQL database and database user in cPanel.
2. Upload or pull the repository into a directory outside `public_html`, for example `~/hydrox-portal`.
3. Point the domain document root to the Laravel `public` directory, or place only the contents of `public` in `public_html` and adjust paths carefully.
4. Create a production `.env` file on the server using `.env.example` as the template.
5. Run Composer install for production:

```bash
composer install --no-dev --optimize-autoloader
```

6. Build frontend assets before deployment or on the server if Node.js is available:

```bash
npm ci
npm run build
```

7. Run Laravel setup commands:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

8. Confirm storage and cache directories are writable by the hosting account.
9. Keep `public/.user.ini` in the live document root so large multi-area photo uploads can exceed PHP's default 20-file limit.
10. Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct `APP_URL`.

## Useful Artisan Commands

```bash
php artisan migrate
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:work
php artisan test
```

## Version Control Notes

The repository intentionally excludes generated dependencies, local secrets, local databases, runtime logs, cached files, and uploaded storage. Install dependencies and generate runtime files separately in each environment.
